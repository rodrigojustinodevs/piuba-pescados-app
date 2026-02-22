<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batche;

use App\Application\DTOs\BatcheDTO;
use App\Domain\Repositories\BatcheRepositoryInterface;
use App\Infrastructure\Mappers\BatcheMapper;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateBatcheUseCase
{
    public function __construct(
        protected BatcheRepositoryInterface $batcheRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): BatcheDTO
    {
        return DB::transaction(function () use ($data): BatcheDTO {
            $mappedData = BatcheMapper::fromRequest($data);

            $tankId = (string) ($mappedData['tank_id'] ?? '');
            $status = (string) ($mappedData['status'] ?? 'active');

            if ($tankId === '') {
                throw new RuntimeException('Invalid batche payload');
            }

            if ($status === 'active' && $this->batcheRepository->hasActiveBatcheInTank($tankId)) {
                throw new RuntimeException('Tank already has an active batche.');
            }

            $batche = $this->batcheRepository->create($mappedData);

            return BatcheMapper::toDTO($batche);
        });
    }
}
