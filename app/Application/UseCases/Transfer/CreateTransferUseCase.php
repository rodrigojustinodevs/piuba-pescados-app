<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\DTOs\TransferInputDTO;
use App\Domain\Models\Transfer;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateTransferUseCase
{
    public function __construct(
        protected TransferRepositoryInterface $transferRepository,
        protected BatchRepositoryInterface $batchRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Transfer
    {
        return DB::transaction(function () use ($data): Transfer {
            $dto               = TransferInputDTO::fromArray($data);
            $originTankId      = $dto->originTankId;
            $destinationTankId = $dto->destinationTankId;
            $batchId           = $dto->batchId;
            $quantity          = $dto->quantity;

            $batch = $this->batchRepository->showBatch('id', $batchId);

            if (! $batch instanceof \App\Domain\Models\Batch) {
                throw new RuntimeException('Batch not found');
            }

            if ((string) $batch->tank_id !== $originTankId) {
                throw new RuntimeException('The batch is not in the origin tank informed.');
            }

            if ($this->batchRepository->hasActiveBatchInTank($destinationTankId, $batchId)) {
                throw new RuntimeException('Tank already has an active batch.');
            }

            $transfer = $this->transferRepository->create($dto->toPersistence());

            $newQuantity  = $batch->initial_quantity - $quantity;
            $updatedBatch = $this->batchRepository->update($batchId, [
                'tank_id'          => $dto->destinationTankId,
                'initial_quantity' => $newQuantity,
            ]);

            if (! $updatedBatch instanceof \App\Domain\Models\Batch) {
                throw new RuntimeException('Error updating batch tank');
            }

            return $transfer;
        });
    }
}
