<?php

declare(strict_types=1);

namespace App\Application\UseCases\SalesOrder;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Enums\SalesOrderType;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SalesOrderRepositoryInterface;

final readonly class ListSalesOrdersUseCase
{
    public function __construct(
        private SalesOrderRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $filters clientId, status, type, perPage, page
     */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['companyId'] = $this->companyResolver->resolve();
        $filters['type']      = SalesOrderType::ORDER->value;

        return $this->repository->paginate($filters);
    }
}
