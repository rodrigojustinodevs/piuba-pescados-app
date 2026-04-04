<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Actions\Client\GuardClientCreditAction;
use App\Application\Actions\Sale\GenerateReceivableAction;
use App\Application\Actions\Sale\GuardBiomassAction;
use App\Application\Actions\Sale\GuardClientFiscalDataAction;
use App\Application\Actions\Sale\HarvestLifecycleAction;
use App\Application\Actions\Sale\RegisterBiomassOutflowAction;
use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\HarvestSaleDTO;
use App\Domain\Events\SaleProcessed;
use App\Domain\Exceptions\ClosedStockingException;
use App\Domain\Models\Sale;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Orquestra o fluxo completo de despesca e venda.
 *
 * Regras de negócio aplicadas (em ordem):
 *   1. stocking_id obrigatório — garantido pela SaleStoreRequest (required).
 *   2. Stocking não pode estar fechado.
 *   3. Dados fiscais do cliente (se needs_invoice = true).
 *   4. Limite de crédito do cliente.
 *   5. Biomassa disponível com tolerância de 50% (constante de domínio).
 *   6. Persistência da venda.
 *   7. Baixa de biomassa com CMV exato (RegisterBiomassOutflowAction).
 *   8. Ciclo de vida stocking/batch se is_total_harvest = true (HarvestLifecycleAction).
 *   9. Geração de Contas a Receber (GenerateReceivableAction).
 *  10. Evento SaleProcessed disparado após commit → GenerateStockingHistory.
 *
 * Mudanças em relação à versão anterior:
 *   - Stocking carregado via StockingRepositoryInterface (não mais Stocking::findOrFail diretamente).
 *   - CloseStockingAndBatchAction substituída por HarvestLifecycleAction (unificação create/update).
 *   - HarvestSaleDTO construído com fromValidated() em vez de fromArray() — normalização feita na Request.
 *   - StockingRequiredException removida: stocking_id é `required` na Request, nunca chega null aqui.
 *   - company_id resolvido antes de construir o DTO, mantendo o DTO sem dependência do resolver.
 */
final class ProcessHarvestSaleUseCase
{
    /**
     * Regra 5: tolerância máxima acima da biomassa estimada permitida na despesca.
     * Constante de domínio — não lida do payload.
     */
    private const float BIOMASS_TOLERANCE_PERCENT = 50.0;

    public function __construct(
        private readonly SaleRepositoryInterface      $saleRepository,
        private readonly StockingRepositoryInterface  $stockingRepository,
        private readonly CompanyResolverInterface     $companyResolver,
        private readonly GuardClientFiscalDataAction  $guardFiscalData,
        private readonly GuardClientCreditAction      $guardClientCredit,
        private readonly GuardBiomassAction           $guardBiomass,
        private readonly RegisterBiomassOutflowAction $registerOutflow,
        private readonly HarvestLifecycleAction       $harvestLifecycle,
        private readonly GenerateReceivableAction     $generateReceivable,
    ) {}

    /**
     * @param array<string, mixed> $data Array validado e normalizado pela SaleStoreRequest.
     *
     * @throws ClosedStockingException
     * @throws \App\Domain\Exceptions\ClientMissingFiscalDataException
     * @throws \App\Domain\Exceptions\ClientCreditLimitExceededException
     * @throws \App\Domain\Exceptions\InsufficientBiomassException
     */
    public function execute(array $data): Sale
    {
        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? null,
        );

        $dto = HarvestSaleDTO::fromValidated($data);

        return DB::transaction(fn (): Sale => $this->process($dto));
    }

    private function process(HarvestSaleDTO $dto): Sale
    {
        // ── 1. Carrega e valida o stocking ────────────────────────────────────
        $stocking = $this->stockingRepository->findOrFail($dto->stockingId);

        if ($stocking->isClosed()) {
            throw new ClosedStockingException($stocking->id);
        }

        // ── 2. Validações pré-persistência (sem escrita no banco) ─────────────
        $this->guardFiscalData->execute($dto->clientId, $dto->needsInvoice);
        $this->guardClientCredit->execute($dto->clientId, $dto->totalRevenue());
        $this->guardBiomass->executeWithTolerance(
            stocking:         $stocking,
            requestedWeight:  $dto->totalWeight,
            tolerancePercent: self::BIOMASS_TOLERANCE_PERCENT,
        );

        // ── 3. Persiste a venda ───────────────────────────────────────────────
        $sale = $this->saleRepository->create($dto->toSaleInputDTO());

        // ── 4. Baixa de biomassa com CMV exato (Regra 3) ─────────────────────
        // Peso já vendido ANTES desta venda (exclui a atual para não contaminar o CMV)
        $alreadySoldWeight = $this->saleRepository->soldWeightByStocking(
            stockingId:    $dto->stockingId,
            excludeSaleId: (string) $sale->id,
        );

        $this->registerOutflow->execute($stocking, $sale, $alreadySoldWeight);

        // ── 5. Ciclo de vida stocking/batch (Regra 4) ─────────────────────────
        // HarvestLifecycleAction é idempotente: oldHarvest=false, newHarvest=dto->isHarvestTotal
        if ($dto->isHarvestTotal) {
            $this->harvestLifecycle->apply(
                stocking:          $stocking,
                oldIsTotalHarvest: false,
                newIsTotalHarvest: true,
                batchId:           $dto->batchId,
            );
        }

        // ── 6. Contas a Receber (Regra 5) ─────────────────────────────────────
        $this->generateReceivable->execute($dto->toSaleInputDTO(), $sale);

        // Disparado após commit — listener GenerateStockingHistory cria o histórico
        SaleProcessed::dispatch($sale);

        return $sale;
    }
}