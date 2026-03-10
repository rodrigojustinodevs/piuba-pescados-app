<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\DTOs\TransferDTO;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use App\Infrastructure\Mappers\TransferMapper;
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
    public function execute(array $data): TransferDTO
    {
        return DB::transaction(function () use ($data): TransferDTO {
            $mappedData        = TransferMapper::fromRequest($data);
            $originTankId      = (string) $mappedData['origin_tank_id'];
            $destinationTankId = (string) $mappedData['destination_tank_id'];
            $batchId           = (string) $mappedData['batch_id'];
            $quantity          = (int) $mappedData['quantity'];

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

            $transfer = $this->transferRepository->create($mappedData);

            $newQuantity  = $batch->initial_quantity - $quantity;
            $updatedBatch = $this->batchRepository->update($batchId, [
                'tank_id'          => $mappedData['destination_tank_id'],
                'initial_quantity' => $newQuantity,
            ]);

            if (! $updatedBatch instanceof \App\Domain\Models\Batch) {
                throw new RuntimeException('Error updating batch tank');
            }

            return TransferMapper::toDTO($transfer);
        });
    }
}
