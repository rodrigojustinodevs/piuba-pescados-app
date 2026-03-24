<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialTransaction;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final readonly class ListFinancialTransactionsUseCase
{
    public function __construct(
        private FinancialTransactionRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['company_id'] = $this->companyResolver->resolve();

        return $this->repository->paginate($filters);
    }
}
