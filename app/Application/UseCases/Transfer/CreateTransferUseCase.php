<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\DTOs\TransferDTO;
use App\Domain\Repositories\BatcheRepositoryInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use Illuminate\Support\Facades\DB;

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
            $transfer = $this->transferRepository->create($data);

            $this->batcheRepository->update($data['batche_id'], [
                'tank_id' => $data['destination_tank_id'],
            ]);

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
