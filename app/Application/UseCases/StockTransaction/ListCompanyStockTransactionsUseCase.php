<?php

declare(strict_types=1);

namespace App\Application\UseCases\StockTransaction;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockTransactionRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;

final readonly class ListCompanyStockTransactionsUseCase
{
    public function __construct(
        private StockTransactionRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array{
     *     direction?: string|null,
     *     reference_type?: string|null,
     *     reference_id?: string|null,
     *     per_page?: int,
     *     page?: int,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        if (! CompanyContext::isMasterAdmin()) {
            $filters['companyId'] = CompanyContext::requireCompanyId();
        }

        return $this->repository->paginate($filters);
    }
}
