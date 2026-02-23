<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\DTOs\TransferDTO;
use App\Domain\Repositories\BatcheRepositoryInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use App\Infrastructure\Mappers\TransferMapper;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateTransferUseCase
{
    public function __construct(
        protected TransferRepositoryInterface $transferRepository,
        protected BatcheRepositoryInterface $batcheRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): TransferDTO
    {
        return DB::transaction(function () use ($data): TransferDTO {
            $mappedData = TransferMapper::fromRequest($data);

            $originTankId      = (string) ($mappedData['origin_tank_id'] ?? '');
            $destinationTankId = (string) ($mappedData['destination_tank_id'] ?? '');
            $batcheId          = (string) ($mappedData['batche_id'] ?? '');

            if ($originTankId === '' || $destinationTankId === '' || $batcheId === '') {
                throw new RuntimeException('Invalid transfer payload');
            }

            if ($originTankId === $destinationTankId) {
                throw new RuntimeException('The origin tank cannot be the same as the destination tank.');
            }

            $batche = $this->batcheRepository->showBatche('id', $batcheId);

            if (! $batche) {
                throw new RuntimeException('Batche not found');
            }

            if ((string) $batche->tank_id !== $originTankId) {
                throw new RuntimeException('The batche is not in the origin tank informed.');
            }

            if ($this->batcheRepository->hasActiveBatcheInTank($destinationTankId, $batcheId)) {
                throw new RuntimeException('Tank already has an active batche.');
            }

            $quantity = (int) ($mappedData['quantity'] ?? 0);
            if ($quantity <= 0) {
                throw new RuntimeException('Transfer quantity must be greater than zero.');
            }
            // if ($batche->initial_quantity < $quantity) {
            //     throw new RuntimeException(
            //         'Insufficient quantity in batch. Available: ' . $batche->initial_quantity . ', requested: ' . $quantity . '.'
            //     );
            // }

            $transfer = $this->transferRepository->create($mappedData);

            $newQuantity = $batche->initial_quantity - $quantity;
            $updatedBatche = $this->batcheRepository->update($batcheId, [
                'tank_id'          => $mappedData['destination_tank_id'],
                'initial_quantity' => $newQuantity,
            ]);

            if (! $updatedBatche) {
                throw new RuntimeException('Error updating batche tank');
            }

            return TransferMapper::toDTO($transfer);
        });
    }
}
