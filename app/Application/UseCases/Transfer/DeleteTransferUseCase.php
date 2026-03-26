<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeleteTransferUseCase
{
    public function __construct(
        protected TransferRepositoryInterface $transferRepository,
        protected BatchRepositoryInterface $batchRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $transfer = $this->transferRepository->showTransfer('id', $id);

            if (! $transfer instanceof \App\Domain\Models\Transfer) {
                return false;
            }

            $batch = $this->batchRepository->showBatch('id', $transfer->batch_id);

            if ($batch instanceof \App\Domain\Models\Batch) {
                $originHasOtherBatch = $this->batchRepository->hasActiveBatchInTank(
                    $transfer->origin_tank_id,
                    $transfer->batch_id
                );

                if ($originHasOtherBatch) {
                    throw new RuntimeException(
                        'Cannot delete transfer: origin tank already has an active batch.'
                    );
                }

                $newQuantity = $batch->initial_quantity + (int) $transfer->quantity;
                $this->batchRepository->update($transfer->batch_id, [
                    'tank_id'          => $transfer->origin_tank_id,
                    'initial_quantity' => $newQuantity,
                ]);
            }

            return $this->transferRepository->delete($id);
        });
    }
}
