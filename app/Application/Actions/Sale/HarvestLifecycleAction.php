<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Enums\BatchStatus;
use App\Domain\Models\Batch;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;

/**
 * Action que gerencia transições de ciclo de vida do stocking/batch
 * quando o campo `is_total_harvest` de uma venda é alterado.
 *
 * Substitui a dupla CloseStockingAndBatchAction + ReopenStockingAndBatchAction,
 * que eram dois lados da mesma responsabilidade e dependiam uma da outra
 * contextualmente (o UseCase decidia qual chamar).
 *
 * Regras de negócio encapsuladas:
 *   - Ativar despesca total  → fecha stocking; fecha batch se não houver outros ativos
 *   - Reverter despesca total → reabre stocking; reabre batch + tank se batch estava fechado
 *
 * Chamada dentro de DB::transaction — atomicidade garantida pelo chamador.
 */
final readonly class HarvestLifecycleAction
{
    public function __construct(
        private StockingRepositoryInterface $stockingRepository,
        private BatchRepositoryInterface $batchRepository,
        private TankRepositoryInterface $tankRepository,
    ) {
    }

    /**
     * Aplica a transição de ciclo de vida adequada com base na mudança de `is_total_harvest`.
     *
     * Idempotente: se old === new, nenhuma escrita é realizada.
     */
    public function apply(
        Stocking $stocking,
        bool $oldIsTotalHarvest,
        bool $newIsTotalHarvest,
        string $batchId,
    ): void {
        if ($oldIsTotalHarvest === $newIsTotalHarvest) {
            return;
        }

        if ($oldIsTotalHarvest && ! $newIsTotalHarvest) {
            $this->reopen($stocking, $batchId);

            return;
        }

        if (! $oldIsTotalHarvest && $newIsTotalHarvest && ! $stocking->isClosed()) {
            $this->close($stocking);
        }
    }

    /**
     * Encerra stocking e, se necessário, encerra o batch.
     *
     * Regra: só encerra o batch quando não existirem outros stockings ativos no mesmo lote.
     */
    private function close(Stocking $stocking): void
    {
        $stocking->markAsClosed();

        $hasOtherActiveStockings = $this->stockingRepository->hasActiveStockingsInBatch(
            batchId:           (string) $stocking->batch_id,
            excludeStockingId: (string) $stocking->id,
        );

        if (! $hasOtherActiveStockings) {
            $this->batchRepository->update((string) $stocking->batch_id, [
                'status' => BatchStatus::FINISHED->value,
            ]);
        }
    }

    /**
     * Reabre stocking e, se o batch estava encerrado, reabre batch + tank.
     *
     * Nota: o tank é reaberto aqui (não no CloseStockingAndBatchAction original)
     * porque o encerramento do tank é consequência do encerramento do batch —
     * a reversão deve ser simétrica.
     */
    private function reopen(Stocking $stocking, string $batchId): void
    {
        $this->stockingRepository->update((string) $stocking->id, [
            'status'    => 'active',
            'closed_at' => null,
        ]);

        /** @var Batch $batch */
        $batch = $this->batchRepository->findOrFail($batchId);

        if (! $batch->isFinished()) {
            return;
        }

        $this->batchRepository->update($batchId, [
            'status' => BatchStatus::ACTIVE->value,
        ]);

        $this->tankRepository->update((string) $batch->tank_id, [
            'status' => 'active',
        ]);
    }
}
