<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Enums\BatchStatus;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;

final readonly class CloseStockingAndBatchAction
{
    public function __construct(
        private StockingRepositoryInterface $stockingRepository,
        private BatchRepositoryInterface $batchRepository,
    ) {
    }

    /**
     * Regra 4 da despesca total:
     *  1. Encerra o status do povoamento (stocking).
     *  2. SE não houver outros povoamentos ativos no mesmo lote → encerra o lote também.
     *
     * Chamada dentro da DB::transaction do UseCase — atomicidade garantida.
     */
    public function execute(Stocking $stocking): void
    {
        // Passo 1: Encerra o povoamento
        $stocking->markAsClosed();

        // Passo 2: Verifica se existem outros povoamentos ativos no lote
        $hasOtherActiveStockings = $this->stockingRepository
            ->hasActiveStockingsInBatch(
                batchId:          (string) $stocking->batch_id,
                excludeStockingId: (string) $stocking->id,
            );

        if (! $hasOtherActiveStockings) {
            // Não há mais nenhum povoamento ativo — encerra o lote também
            $this->batchRepository->update((string) $stocking->batch_id, [
                'status' => BatchStatus::FINISHED->value,
            ]);
        }
    }
}
