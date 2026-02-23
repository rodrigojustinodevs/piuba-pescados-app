<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Domain\Repositories\BatcheRepositoryInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeleteTransferUseCase
{
    public function __construct(
        protected TransferRepositoryInterface $transferRepository,
        protected BatcheRepositoryInterface $batcheRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $transfer = $this->transferRepository->showTransfer('id', $id);

            if (! $transfer) {
                return false;
            }

            $batche = $this->batcheRepository->showBatche('id', $transfer->batche_id);

            if ($batche) {
                if ($this->batcheRepository->hasActiveBatcheInTank($transfer->origin_tank_id, $transfer->batche_id)) {
                    throw new RuntimeException(
                        'Cannot delete transfer: origin tank already has an active batch. One tank can only have one active batch.'
                    );
                }

                $newQuantity = $batche->initial_quantity + (int) $transfer->quantity;
                $updated = $this->batcheRepository->update($transfer->batche_id, [
                    'tank_id'          => $transfer->origin_tank_id,
                    'initial_quantity' => $newQuantity,
                ]);

                if (! $updated) {
                    throw new RuntimeException('Error reverting batch to origin tank');
                }
            }

            return $this->transferRepository->delete($id);
        });
    }
}
