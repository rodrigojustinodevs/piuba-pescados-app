<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedInventory;

use App\Domain\Models\FeedInventory;
use App\Domain\Repositories\FeedInventoryRepositoryInterface;

final readonly class ShowFeedInventoryUseCase
{
    public function __construct(
        private FeedInventoryRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): FeedInventory
    {
        return $this->repository->findOrFail($id);
    }
}
