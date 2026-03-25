<?php

declare(strict_types=1);

namespace App\Application\UseCases\StockingHistory;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockingHistoryRepositoryInterface;

final readonly class ListStockingHistoriesUseCase
{
    public function __construct(
        private StockingHistoryRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array{
     *     stocking_id?: string|null,
     *     event?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['company_id'] = $this->companyResolver->resolve();

        return $this->repository->paginate($filters);
    }
}
