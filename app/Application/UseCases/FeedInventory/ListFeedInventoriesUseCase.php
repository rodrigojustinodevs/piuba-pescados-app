<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedInventory;

use App\Domain\Repositories\FeedInventoryRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final readonly class ListFeedInventoriesUseCase
{
    public function __construct(
        private FeedInventoryRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        return $this->repository->paginate($filters);
    }
}
