<?php

declare(strict_types=1);

namespace App\Application\UseCases\CostAllocation;

use App\Domain\Repositories\CostAllocationRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteCostAllocationUseCase
{
    public function __construct(
        private CostAllocationRepositoryInterface $repository,
        private StockingRepositoryInterface $stockingRepository,
    ) {
    }

    /**
     * Reversal: undoes all side-effects created during allocation and soft-deletes the record.
     *
     * Reversal order:
     * 1. Decrement accumulated_fixed_cost on all stockings — 1 batch query.
     * 2. Release the financial transaction (is_allocated = false).
     * 3. Delete items then soft-delete the allocation header.
     */
    public function execute(string $id): void
    {
        $allocation = $this->repository->findOrFail($id);

        DB::transaction(function () use ($allocation): void {
            // Step 1 — Reverse accumulated_fixed_cost in a single batch query
            $amounts = $allocation->items
                ->mapWithKeys(static fn ($item): array => [
                    (string) $item->stocking_id => (float) $item->amount,
                ])
                ->all();

            $this->stockingRepository->bulkDecrementFixedCost($amounts);

            // Step 2 — Release the expense transaction for re-allocation
            if ($allocation->financialTransaction !== null) {
                $allocation->financialTransaction->update(['is_allocated' => false]);
            }

            // Step 3 — Delete items then soft-delete the allocation header
            $allocation->items()->delete();
            $allocation->delete();
        });
    }
}
