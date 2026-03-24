<?php

declare(strict_types=1);

namespace App\Application\UseCases\CostAllocation;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\CostAllocationRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final readonly class ListCostAllocationsUseCase
{
    public function __construct(
        private CostAllocationRepositoryInterface $repository,
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
