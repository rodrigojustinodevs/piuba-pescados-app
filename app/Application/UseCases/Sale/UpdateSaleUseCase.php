<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Actions\Sale\GuardBiomassAction;
use App\Application\Actions\Sale\HarvestLifecycleAction;
use App\Application\Actions\Sale\SyncReceivableAmountAction;
use App\Application\Services\Sale\SaleRevenueCalculator;
use App\Domain\Models\Sale;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use App\Domain\ValueObjects\SaleAttributes;
use Illuminate\Support\Facades\DB;

/**
 * Orquestra a atualização de uma venda dentro de uma transação única.
 *
 * Campos editáveis: total_weight, price_per_kg, sale_date, is_total_harvest, status, notes.
 * Campos imutáveis: client_id, batch_id, stocking_id (garantido pela SaleUpdateRequest).
 *
 * Responsabilidade deste UseCase: coordenar a sequência correta de ações,
 * garantir a transação e devolver o modelo atualizado.
 *
 * Regras delegadas:
 *  - Biomassa          → GuardBiomassAction
 *  - Ciclo de vida     → HarvestLifecycleAction
 *  - Sync financeiro   → SyncReceivableAmountAction
 *  - Cálculo receita   → SaleRevenueCalculator
 *  - Atributos patch   → SaleAttributes (Value Object)
 */
final class UpdateSaleUseCase
{
    public function __construct(
        private readonly SaleRepositoryInterface     $saleRepository,
        private readonly StockingRepositoryInterface $stockingRepository,
        private readonly GuardBiomassAction          $guardBiomass,
        private readonly HarvestLifecycleAction      $harvestLifecycle,
        private readonly SyncReceivableAmountAction  $syncReceivable,
        private readonly SaleRevenueCalculator       $revenueCalculator,
    ) {}

    /**
     * @param array<string, mixed> $data Array validado e normalizado pela SaleUpdateRequest.
     *
     * @throws \App\Domain\Exceptions\InsufficientBiomassException
     * @throws \App\Domain\Exceptions\SaleFinanciallyLockedException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function execute(string $id, array $data): Sale
    {
        return DB::transaction(function () use ($id, $data): Sale {

            // ── 1. Carrega entidades com locks pessimistas ────────────────────
            $sale       = $this->saleRepository->findOrFailLocked($id);
            $stocking   = $this->resolveLockedStocking($sale);
            $attributes = SaleAttributes::fromValidatedData($data);

            // ── 2. Valida biomassa (só se o peso aumentou) ────────────────────
            $this->guardBiomassIfNeeded($sale, $stocking, $attributes);

            // ── 3. Ciclo de vida stocking/batch ───────────────────────────────
            if ($stocking !== null) {
                $this->harvestLifecycle->apply(
                    stocking:          $stocking,
                    oldIsTotalHarvest: (bool) $sale->is_total_harvest,
                    newIsTotalHarvest: $attributes->resolveIsTotalHarvest($sale),
                    batchId:           (string) $sale->batch_id,
                );
            }

            // ── 4. Receita e sincronização financeira ─────────────────────────
            $newRevenue = $this->revenueCalculator->calculate($sale, $attributes);
            $oldRevenue = round((float) $sale->total_revenue, 2);

            $this->syncReceivable->execute((string) $sale->id, $newRevenue, $oldRevenue);

            if ($this->syncReceivable->hasChanged($newRevenue, $oldRevenue)) {
                $attributes = $attributes->withRevenue($newRevenue);
            }

            // ── 5. Persistência ───────────────────────────────────────────────
            if ($attributes->isEmpty()) {
                return $this->saleRepository->findOrFail($id);
            }

            return $this->saleRepository->update($id, $attributes->toArray());
        });
    }

    // ── Privados ──────────────────────────────────────────────────────────────

    private function resolveLockedStocking(Sale $sale): ?Stocking
    {
        if ($sale->stocking_id === null) {
            return null;
        }

        return $this->stockingRepository->findOrFailLocked((string) $sale->stocking_id);
    }

    private function guardBiomassIfNeeded(
        Sale           $sale,
        ?Stocking      $stocking,
        SaleAttributes $attributes,
    ): void {
        if ($stocking === null || ! $attributes->has('total_weight')) {
            return;
        }

        $newWeight = $attributes->resolveWeight($sale);
        $oldWeight = (float) $sale->total_weight;

        if ($newWeight > $oldWeight) {
            $this->guardBiomass->execute($stocking, $newWeight, (string) $sale->id);
        }
    }
}