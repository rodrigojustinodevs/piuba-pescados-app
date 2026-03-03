<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Application\DTOs\BatchDTO;
use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Infrastructure\Mappers\BatchMapper;
use RuntimeException;

class ShowBatchUseCase
{
    public function __construct(
        protected BatchRepositoryInterface $batchRepository
    ) {
    }

    public function execute(string $id): ?BatchDTO
    {
        $batch = $this->batchRepository->showBatch('id', $id);

        if (! $batch instanceof Batch) {
            throw new RuntimeException('Batch not found');
        }

        return BatchMapper::toDTO($batch);
    }
}
