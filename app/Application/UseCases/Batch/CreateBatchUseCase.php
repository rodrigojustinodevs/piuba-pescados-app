<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Application\DTOs\BatchInputDTO;
use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
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
    public function execute(array $data): Batch
    {
        return DB::transaction(function () use ($data): Batch {
            $dto = BatchInputDTO::fromArray($data);

            $tankId = $dto->tankId ?? '';
            $status = $dto->status;

            if ($tankId === '') {
                throw new RuntimeException('Invalid batch payload');
            }

            if ($status === 'active' && $this->batchRepository->hasActiveBatchInTank($tankId)) {
                throw new RuntimeException('Tank already has an active batch.');
            }

            return $this->batchRepository->create($dto->toPersistence());
        });
    }
}
