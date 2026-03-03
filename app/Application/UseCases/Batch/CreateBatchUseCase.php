<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Application\DTOs\BatchDTO;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Infrastructure\Mappers\BatchMapper;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateBatchUseCase
{
    public function __construct(
        protected BatchRepositoryInterface $batchRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): BatchDTO
    {
        return DB::transaction(function () use ($data): BatchDTO {
            $mappedData = BatchMapper::fromRequest($data);

            $tankId = (string) ($mappedData['tank_id'] ?? '');
            $status = (string) ($mappedData['status'] ?? 'active');

            if ($tankId === '') {
                throw new RuntimeException('Invalid batch payload');
            }

            if ($status === 'active' && $this->batchRepository->hasActiveBatchInTank($tankId)) {
                throw new RuntimeException('Tank already has an active batch.');
            }

            $batch = $this->batchRepository->create($mappedData);

            return BatchMapper::toDTO($batch);
        });
    }
}
