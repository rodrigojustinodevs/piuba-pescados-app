<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedInventory;

use App\Domain\Repositories\FeedInventoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteFeedInventoryUseCase
{
    public function __construct(
        private FeedInventoryRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): void
    {
        DB::transaction(function () use ($id): void {
            $this->repository->findOrFail($id);
            $this->repository->delete($id);
        });
    }
}
