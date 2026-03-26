<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final readonly class ListFeedingsUseCase
{
    public function __construct(
        private FeedingRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array{
     *     batch_id?: string|null,
     *     feed_type?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        return $this->repository->paginate($filters);
    }
}
