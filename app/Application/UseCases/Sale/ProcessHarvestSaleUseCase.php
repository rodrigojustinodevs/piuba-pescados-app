<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Actions\Client\GuardClientCreditAction;
use App\Application\Actions\FinancialTransaction\GenerateReceivableAction;
use App\Application\Actions\Sale\GuardBiomassAction;
use App\Application\Actions\Sale\GuardClientFiscalDataAction;
use App\Application\Actions\Sale\HarvestLifecycleAction;
use App\Application\Actions\Sale\RegisterBiomassOutflowAction;
use App\Application\DTOs\HarvestSaleDTO;
use App\Application\DTOs\SaleItemDTO;
use App\Domain\Models\Stocking;
use App\Application\Services\Sale\SaleCodeGeneratorService;
use App\Domain\Events\SaleProcessed;
use App\Domain\Exceptions\ClosedStockingException;
use App\Domain\Models\Sale;
use App\Domain\Models\SaleItem;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Orquestra o fluxo completo de despesca e venda.
 *
 * Suporta múltiplos itens (produtos/lotes) por venda.
 * Cada item passa pelas mesmas validações de biomassa e gera sua própria baixa de estoque.
 *
 * Regras aplicadas em ordem:
 *  1. Para cada item: stocking não pode estar fechado + biomassa disponível (50% tolerância).
 *  2. Dados fiscais do cliente (se needs_invoice = true).
 *  3. Limite de crédito do cliente (receita total da venda).
 *  4. Persistência da venda com todos os itens.
 *  5. Para cada item: baixa de biomassa com CMV exato.
 *  6. Para cada item: ciclo de vida stocking/batch se is_total_harvest = true.
 *  7. Contas a Receber (receita total).
 *  8. Evento SaleProcessed após commit.
 */
final readonly class ProcessHarvestSaleUseCase
{
    private const float BIOMASS_TOLERANCE_PERCENT = 50.0;

    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private StockingRepositoryInterface $stockingRepository,
        private GuardClientFiscalDataAction $guardFiscalData,
        private GuardClientCreditAction $guardClientCredit,
        private GuardBiomassAction $guardBiomass,
        private RegisterBiomassOutflowAction $registerOutflow,
        private HarvestLifecycleAction $harvestLifecycle,
        private GenerateReceivableAction $generateReceivable,
        private SaleCodeGeneratorService $codeGenerator,
        private BatchRepositoryInterface $batchRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Sale
    {
        // company_id é derivado do batch do primeiro item
        $batchId = $data['items'][0]['batch_id']
            ?? $data['batch_id']
            ?? '';

        $data['company_id'] = $this->batchRepository->findOrFail((string) $batchId)->tank->company_id;

        $dto = HarvestSaleDTO::fromValidated($data);

        return DB::transaction(fn (): Sale => $this->process($dto));
    }

    private function process(HarvestSaleDTO $dto): Sale
    {
        // ── 1. Validações por item ─────────────────────────────────────────────
        foreach ($dto->items as $itemDto) {
            /** @var SaleItemDTO $itemDto */
            $stocking = $this->stockingRepository->findOrFail($itemDto->stockingId);

            if ($stocking->isClosed()) {
                throw new ClosedStockingException($stocking->id);
            }

            $this->guardBiomass->executeWithTolerance(
                stocking:         $stocking,
                requestedWeight:  $itemDto->totalWeight,
                tolerancePercent: self::BIOMASS_TOLERANCE_PERCENT,
                excludeSaleId:    null,
            );
        }

        // ── 2. Validações globais ──────────────────────────────────────────────
        $this->guardFiscalData->execute($dto->clientId, $dto->needsInvoice);
        $this->guardClientCredit->execute($dto->clientId, $dto->totalRevenue());

        // ── 3. Persistência da venda e itens ──────────────────────────────────
        $saleInput = $dto->toSaleInputDTO();
        $sale      = $this->saleRepository->create($saleInput);
        $code      = $this->codeGenerator->generate($saleInput->companyId);
        $sale      = $this->saleRepository->update((string) $sale->id, ['code' => $code]);

        // ── 4. Baixa de biomassa por item ──────────────────────────────────────
        foreach ($dto->items as $itemDto) {
            /** @var SaleItemDTO $itemDto */
            $this->processItemOutflow($sale, $itemDto);
        }

        // ── 5. Contas a Receber ────────────────────────────────────────────────
        $this->generateReceivable->execute($dto->toSaleInputDTO(), (string) $sale->id);

        SaleProcessed::dispatch($sale);

        return $sale;
    }

    private function processItemOutflow(Sale $sale, SaleItemDTO $itemDto): void
    {
        $stocking = $this->stockingRepository->findOrFail($itemDto->stockingId);

        /** @var SaleItem|null $saleItem */
        $saleItem = $sale->items->firstWhere('stocking_id', $itemDto->stockingId);

        if ($saleItem === null) {
            return;
        }

        $alreadySoldWeight = $this->saleRepository->soldWeightByStocking(
            stockingId:    $itemDto->stockingId,
            excludeSaleId: (string) $sale->id,
        );

        $this->registerOutflow->execute($stocking, $sale, $saleItem, $alreadySoldWeight);

        if ($itemDto->isHarvestTotal) {
            $this->harvestLifecycle->apply(
                stocking:          $stocking,
                oldIsTotalHarvest: false,
                newIsTotalHarvest: true,
                batchId:           $itemDto->batchId,
            );
        }
    }
}
