<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Domain\Repositories\BatchRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteBatchUseCase
{
    public function __construct(
        private BatchRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): void
    {
        $this->repository->findOrFail($id);

        DB::transaction(fn (): bool => $this->repository->delete($id));
    }
}
