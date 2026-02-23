<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\DTOs\TransferDTO;
use App\Domain\Models\Transfer;
use App\Domain\Repositories\BatcheRepositoryInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use App\Infrastructure\Mappers\TransferMapper;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateTransferUseCase
{
    public function __construct(
        protected TransferRepositoryInterface $transferRepository,
        protected BatcheRepositoryInterface $batcheRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): TransferDTO
    {
        return DB::transaction(function () use ($id, $data): TransferDTO {
            $mappedData = TransferMapper::fromRequest($data);
            $current    = $this->transferRepository->showTransfer('id', $id);

            if (! $current instanceof Transfer) {
                throw new RuntimeException('Transfer not found');
            }

            $newOriginId      = $mappedData['origin_tank_id'] ?? $current->origin_tank_id;
            $newDestinationId = $mappedData['destination_tank_id'] ?? $current->destination_tank_id;

            if ($newOriginId === $newDestinationId) {
                throw new RuntimeException('The origin tank cannot be the same as the destination tank.');
            }

            $destinationChanged = isset($mappedData['destination_tank_id'])
                && (string) $newDestinationId !== (string) $current->destination_tank_id;

            if ($destinationChanged && $this->batcheRepository->hasActiveBatcheInTank($newDestinationId, $current->batche_id)) {
                throw new RuntimeException('Tank already has an active batche.');
            }

            $transfer = $this->transferRepository->update($id, $mappedData);

            if (! $transfer instanceof Transfer) {
                throw new RuntimeException('Transfer not found');
            }

            if (isset($mappedData['batche_id']) || isset($mappedData['destination_tank_id'])) {
                $batcheIdToUpdate = $mappedData['batche_id'] ?? $transfer->batche_id;

                $this->batcheRepository->update($batcheIdToUpdate, [
                    'tank_id' => $transfer->destination_tank_id,
                ]);
            }

            return TransferMapper::toDTO($transfer);
        });
    }
}
