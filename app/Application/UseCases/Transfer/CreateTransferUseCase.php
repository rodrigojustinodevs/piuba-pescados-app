<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Application\DTOs\TransferDTO;
use App\Domain\Repositories\BatcheRepositoryInterface;
use App\Domain\Repositories\TransferRepositoryInterface;
use App\Infrastructure\Mappers\TransferMapper;
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
            $mappedData = TransferMapper::fromRequest($data);
            $transfer   = $this->transferRepository->create($mappedData);

            $this->batcheRepository->update($mappedData['batche_id'], [
                'tank_id' => $mappedData['destination_tank_id'],
            ]);

            return TransferMapper::toDTO($transfer);
        });
    }
}
