<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Application\DTOs\BatchInputDTO;
use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
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
    public function execute(string $id, array $data): Batch
    {
        return DB::transaction(function () use ($id, $data): Batch {
            $currentBatch = $this->batchRepository->showBatch('id', $id);

            if (! $currentBatch instanceof Batch) {
                throw new RuntimeException('Batch not found');
            }

            $dto = BatchInputDTO::fromArray($data);

            $targetTankId = (string) ($dto->tankId !== '' ? $dto->tankId : $currentBatch->tank_id);
            $targetStatus = $dto->status ?? $currentBatch->status;

            if ($targetStatus === 'active' && $this->batchRepository->hasActiveBatchInTank($targetTankId, $id)) {
                throw new RuntimeException('Tank already has an active batch.');
            }

            $batch = $this->batchRepository->update($id, $dto->toPersistence());

            if (! $batch instanceof Batch) {
                throw new RuntimeException('Batch not found');
            }

            return $batch;
        });
    }
}
