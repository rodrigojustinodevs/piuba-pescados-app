<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Application\DTOs\BatchDTO;
use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Infrastructure\Mappers\BatchMapper;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateBatchUseCase
{
    public function __construct(
        protected BatchRepositoryInterface $batchRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): BatchDTO
    {
        return DB::transaction(function () use ($id, $data): BatchDTO {
            $currentBatch = $this->batchRepository->showBatch('id', $id);

            if (! $currentBatch instanceof Batch) {
                throw new RuntimeException('Batch not found');
            }

            $mappedData = BatchMapper::fromRequest($data);

            $targetTankId = (string) ($mappedData['tank_id'] ?? $currentBatch->tank_id);
            $targetStatus = (string) ($mappedData['status'] ?? $currentBatch->status);

            if ($targetStatus === 'active' && $this->batchRepository->hasActiveBatchInTank($targetTankId, $id)) {
                throw new RuntimeException('Tank already has an active batch.');
            }

            $batch = $this->batchRepository->update($id, $mappedData);

            if (! $batch instanceof Batch) {
                throw new RuntimeException('Batch not found');
            }

            return BatchMapper::toDTO($batch);
        });
    }
}
