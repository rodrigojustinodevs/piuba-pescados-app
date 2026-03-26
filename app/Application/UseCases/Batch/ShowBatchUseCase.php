<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
use RuntimeException;

class ShowBatchUseCase
{
    public function __construct(
        protected BatchRepositoryInterface $batchRepository
    ) {
    }

    public function execute(string $id): Batch
    {
        $batch = $this->batchRepository->showBatch('id', $id);

        if (! $batch instanceof Batch) {
            throw new RuntimeException('Batch not found');
        }

        return $batch;
    }
}
