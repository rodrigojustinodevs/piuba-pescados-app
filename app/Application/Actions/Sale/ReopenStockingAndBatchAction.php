<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Enums\BatchStatus;
use App\Domain\Models\Batch;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;

final readonly class ReopenStockingAndBatchAction
{
    public function __construct(
        private StockingRepositoryInterface $stockingRepository,
        private BatchRepositoryInterface $batchRepository,
        private TankRepositoryInterface $tankRepository,
    ) {
    }

    /**
     * Reverte o encerramento de um despesca total:
     *  1. Reabre o stocking (status → active, cleared_at → null)
     *  2. Se o batch estava encerrado por causa deste stocking → reabre o batch
     *  3. Se o batch foi reaberto → reabre o tank associado
     *
     * Chamado dentro da DB::transaction do UpdateSaleUseCase — atomicidade garantida.
     */
    public function execute(Stocking $stocking, string $batchId): void
    {
        // Passo 1: Reabre o stocking
        $this->stockingRepository->update((string) $stocking->id, [
            'status'    => 'active',
            'closed_at' => null,
        ]);

        // Passo 2: Reabre o batch se estava encerrado
        /** @var Batch $batch */
        $batch = $this->batchRepository->findOrFail($batchId);

        if ($batch->isFinished()) {
            $this->batchRepository->update($batchId, [
                'status' => BatchStatus::ACTIVE->value,
            ]);

            // Passo 3: Reabre o tank associado ao batch
            $this->tankRepository->update((string) $batch->tank_id, [
                'status' => 'active',
            ]);
        }
    }
}
