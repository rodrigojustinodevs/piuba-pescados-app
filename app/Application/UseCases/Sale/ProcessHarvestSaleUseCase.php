<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Actions\Client\GuardClientCreditAction;
use App\Application\Actions\FinancialTransaction\GenerateReceivableAction;
use App\Application\Actions\Sale\GuardBiomassAction;
use App\Application\Actions\Sale\GuardClientFiscalDataAction;
use App\Application\Actions\Sale\HarvestLifecycleAction;
use App\Application\Actions\Sale\RegisterBiomassOutflowAction;
use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\HarvestSaleDTO;
use App\Domain\Events\SaleProcessed;
use App\Domain\Exceptions\ClosedStockingException;
use App\Domain\Models\Sale;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Orquestra o fluxo completo de despesca e venda.
 *
 * Regras aplicadas em ordem:
 *  1. stocking_id obrigatório — garantido pela SaleStoreRequest (required).
 *  2. Stocking não pode estar fechado.
 *  3. Dados fiscais do cliente (se needs_invoice = true).
 *  4. Limite de crédito do cliente.
 *  5. Biomassa disponível com tolerância de 50%.
 *  6. Persistência da venda.
 *  7. Baixa de biomassa com CMV exato (RegisterBiomassOutflowAction).
 *  8. Ciclo de vida stocking/batch se is_total_harvest = true (HarvestLifecycleAction).
 *  9. Contas a Receber (GenerateReceivableAction).
 * 10. Evento SaleProcessed após commit.
 */
final readonly class ProcessHarvestSaleUseCase
{
    private const float BIOMASS_TOLERANCE_PERCENT = 50.0;

    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private StockingRepositoryInterface $stockingRepository,
        private CompanyResolverInterface $companyResolver,
        private GuardClientFiscalDataAction $guardFiscalData,
        private GuardClientCreditAction $guardClientCredit,
        private GuardBiomassAction $guardBiomass,
        private RegisterBiomassOutflowAction $registerOutflow,
        private HarvestLifecycleAction $harvestLifecycle,
        private GenerateReceivableAction $generateReceivable,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Sale
    {
        $data['company_id'] = $this->companyResolver->resolve(hint: $data['company_id'] ?? null);

        $dto = HarvestSaleDTO::fromValidated($data);

        return DB::transaction(fn (): Sale => $this->process($dto));
    }

    private function process(HarvestSaleDTO $dto): Sale
    {
        // ── 1. Stocking ───────────────────────────────────────────────────────
        $stocking = $this->stockingRepository->findOrFail($dto->stockingId);

        if ($stocking->isClosed()) {
            throw new ClosedStockingException($stocking->id);
        }

        // ── 2. Validações pré-persistência ────────────────────────────────────
        $this->guardFiscalData->execute($dto->clientId, $dto->needsInvoice);
        $this->guardClientCredit->execute($dto->clientId, $dto->totalRevenue());
        $this->guardBiomass->executeWithTolerance(
            stocking:         $stocking,
            requestedWeight:  $dto->totalWeight,
            tolerancePercent: self::BIOMASS_TOLERANCE_PERCENT,
        );

        // ── 3. Venda ──────────────────────────────────────────────────────────
        $sale = $this->saleRepository->create($dto->toSaleInputDTO());

        // ── 4. Baixa de biomassa com CMV exato ────────────────────────────────
        $alreadySoldWeight = $this->saleRepository->soldWeightByStocking(
            stockingId:    $dto->stockingId,
            excludeSaleId: (string) $sale->id,
        );

        $this->registerOutflow->execute($stocking, $sale, $alreadySoldWeight);

        // ── 5. Ciclo de vida stocking/batch ───────────────────────────────────
        if ($dto->isHarvestTotal) {
            $this->harvestLifecycle->apply(
                stocking:          $stocking,
                oldIsTotalHarvest: false,
                newIsTotalHarvest: true,
                batchId:           $dto->batchId,
            );
        }

        // ── 6. Contas a Receber ───────────────────────────────────────────────
        // Assinatura correta: (SaleInputDTO, Sale) — não (SaleInputDTO, string)
        $this->generateReceivable->execute($dto->toSaleInputDTO(), (string) $sale->id);

        SaleProcessed::dispatch($sale);

        return $sale;
    }
}
