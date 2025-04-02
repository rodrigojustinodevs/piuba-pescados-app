<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\DTOs\TransferDTO;
use App\Domain\Models\Transfer;
use App\Domain\Repositories\BatcheRepositoryInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
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
            $transfer = $this->transferRepository->update($id, $data);

            if (! $transfer instanceof Transfer) {
                throw new RuntimeException('Transfer not found');
            }

            if ($transfer->origin_tank_id === $transfer->destination_tank_id) {
                throw new RuntimeException('The origin tank cannot be the same as the destination tank.');
            }

            if (isset($data['batche_id'])) {
                $this->batcheRepository->update($data['batche_id'], [
                    'tank_id' => $data['destination_tank_id'],
                ]);
            }

            return new TransferDTO(
                id: $transfer->id,
                batcheId: $transfer->batche_id,
                originTankId: $transfer->origin_tank_id,
                destinationTankId: $transfer->destination_tank_id,
                quantity: $transfer->quantity,
                description: $transfer->description,
                createdAt: $transfer->created_at?->toDateTimeString(),
                updatedAt: $transfer->updated_at?->toDateTimeString()
            );
        });
    }
}
