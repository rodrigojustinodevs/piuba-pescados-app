<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\DTOs\TransferDTO;
use App\Domain\Repositories\TransferRepositoryInterface;
use RuntimeException;

class ShowTransferUseCase
{
    public function __construct(
        protected TransferRepositoryInterface $transferRepository
    ) {
    }

    public function execute(string $id): ?TransferDTO
    {
        $transfer = $this->transferRepository->showTransfer('id', $id);

        if (! $transfer instanceof \App\Domain\Models\Transfer) {
            throw new RuntimeException('Transfer not found');
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
    }
}
