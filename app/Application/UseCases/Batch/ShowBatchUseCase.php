<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;

final readonly class ShowBatchUseCase
{
    public function __construct(
        private BatchRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): Batch
    {
        return $this->repository->findOrFail($id);
    }
}
