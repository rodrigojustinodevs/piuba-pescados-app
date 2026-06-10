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

        DB::transaction(function () use ($transfer): void {
            // M-07: só reverte movimentação se a transferência estava concluída
            if ($transfer->status === 'completed') {
                $batch = $this->batchRepository->findOrFail((string) $transfer->batch_id);

                $this->applyBatchTransfer->revert(
                    $batch,
                    (string) $transfer->origin_tank_id,
                    (int) $transfer->quantity,
                    $transfer->child_batch_id,
                );
            }

            $this->transferRepository->delete($transfer->id);
        });
    }
}
