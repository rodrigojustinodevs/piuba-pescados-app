<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialCategory;

use App\Domain\Repositories\FinancialCategoryRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Infrastructure\Security\CompanyContext;

final readonly class ListFinancialCategoriesUseCase
{
    public function __construct(
        private FinancialCategoryRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        if (! CompanyContext::isMasterAdmin()) {
            $filters['companyId'] = CompanyContext::requireCompanyId();
        }

        return $this->repository->paginate($filters);
    }
}
