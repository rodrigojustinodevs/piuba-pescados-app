<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Domain\Repositories\MortalityRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final readonly class ListMortalitiesUseCase
{
    public function __construct(
        private MortalityRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array{
     *     batch_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     cause?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        return $this->repository->paginate($filters);
    }
}
