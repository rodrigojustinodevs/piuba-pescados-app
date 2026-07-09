<?php

declare(strict_types=1);

namespace App\Application\UseCases\StockTransaction;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockTransactionRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;

final readonly class ListStockTransactionsUseCase
{
    public function __construct(
        private StockTransactionRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array{
     *     direction?: string|null,
     *     referenceType?: string|null,
     *     referenceId?: string|null,
     *     perPage?: int,
     *     page?: int,
     * } $filters
     */
    public function execute(string $referenceId, array $filters = []): PaginationInterface
    {
        if (! CompanyContext::isMasterAdmin()) {
            $filters['companyId'] = CompanyContext::requireCompanyId();
        }

        $filters['referenceId'] = $referenceId;

        return $this->repository->paginate($filters);
    }
}
