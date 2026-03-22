<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockRepositoryInterface;

final readonly class ListStocksUseCase
{
    public function __construct(
        private StockRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array{
     *     supply_id?: string|null,
     *     supplier_id?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['company_id'] = $this->companyResolver->resolve();

        return $this->repository->paginate($filters);
    }
}
