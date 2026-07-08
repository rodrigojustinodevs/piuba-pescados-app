<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;

final readonly class ListSalesUseCase
{
    public function __construct(
        private SaleRepositoryInterface $repository,
    ) {
    }

    /** @param array<string, mixed> $filters */
    public function execute(array $filters = []): PaginationInterface
    {
        if (! CompanyContext::isMasterAdmin()) {
            $filters['companyId'] = CompanyContext::requireCompanyId();
        }

        return $this->repository->paginate($filters);
    }
}
