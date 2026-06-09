<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;

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
        if (!CompanyContext::isMasterAdmin()) {
            $filters['companyId'] = CompanyContext::requireCompanyId();
        }

        return $this->repository->paginate($filters);
    }
}
