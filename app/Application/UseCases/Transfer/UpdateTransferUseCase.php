<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\DTOs\TransferInputDTO;
use App\Domain\Models\Transfer;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateTransferUseCase
{
    public function __construct(
        protected TransferRepositoryInterface $transferRepository,
        protected BatchRepositoryInterface $batchRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): Transfer
    {
        return DB::transaction(function () use ($id, $data): Transfer {
            $dto     = TransferInputDTO::fromArray($data);
            $current = $this->transferRepository->showTransfer('id', $id);

            if (! $current instanceof Transfer) {
                throw new RuntimeException('Transfer not found');
            }

            $newOriginId      = $dto->originTankId ?: $current->origin_tank_id;
            $newDestinationId = $dto->destinationTankId ?: $current->destination_tank_id;

            if ($newOriginId === $newDestinationId) {
                throw new RuntimeException('The origin tank cannot be the same as the destination tank.');
            }

            $destinationChanged = $dto->destinationTankId !== ''
                && (string) $newDestinationId !== (string) $current->destination_tank_id;

            $destinationHasOtherBatch = $this->batchRepository->hasActiveBatchInTank(
                $newDestinationId,
                $current->batch_id
            );

            if ($destinationChanged && $destinationHasOtherBatch) {
                throw new RuntimeException('Tank already has an active batch.');
            }

            $transfer = $this->transferRepository->update($id, $dto->toPersistence());

            if (! $transfer instanceof Transfer) {
                throw new RuntimeException('Transfer not found');
            }

            if ($dto->batchId !== '' || $dto->destinationTankId !== '') {
                $batchIdToUpdate = $dto->batchId !== '' ? $dto->batchId : $transfer->batch_id;

                $this->batchRepository->update($batchIdToUpdate, [
                    'tank_id' => $transfer->destination_tank_id,
                ]);
            }

            return $transfer;
        });
    }
}
