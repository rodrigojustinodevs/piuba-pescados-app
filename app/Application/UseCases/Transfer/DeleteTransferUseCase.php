<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\Actions\Transfer\ApplyBatchTransferAction;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteTransferUseCase
{
    public function __construct(
        private TransferRepositoryInterface $transferRepository,
        private BatchRepositoryInterface $batchRepository,
        private ApplyBatchTransferAction $applyBatchTransfer,
    ) {
    }

    public function execute(string $id): void
    {
        $transfer = $this->transferRepository->findOrFail($id);
        $batch    = $this->batchRepository->findOrFail((string) $transfer->batch_id);

        DB::transaction(function () use ($transfer, $batch): void {
            // Reverte o lote ao tanque de origem e devolve a quantidade
            $this->applyBatchTransfer->revert(
                batchId:             (string) $transfer->batch_id,
                originTankId:        (string) $transfer->origin_tank_id,
                transferredQuantity: (int) $transfer->quantity,
                currentQuantity:     (int) $batch->initial_quantity,
            );

            $this->transferRepository->delete($transfer->id);
        });
    }
}
