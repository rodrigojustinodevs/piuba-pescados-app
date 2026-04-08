<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SaleRepositoryInterface;

final readonly class ListSalesUseCase
{
    public function __construct(
        private SaleRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /** @param array<string, mixed> $filters */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['company_id'] = $this->companyResolver->resolve();

        return $this->repository->paginate($filters);
    }
}
