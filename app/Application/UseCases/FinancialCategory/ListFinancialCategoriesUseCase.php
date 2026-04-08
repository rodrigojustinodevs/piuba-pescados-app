<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialCategory;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final readonly class ListFinancialCategoriesUseCase
{
    public function __construct(
        private FinancialCategoryRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['companyId'] = $this->companyResolver->resolve();

        return $this->repository->paginate($filters);
    }
}
