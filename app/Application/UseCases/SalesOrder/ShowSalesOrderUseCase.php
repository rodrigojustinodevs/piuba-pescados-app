<?php

declare(strict_types=1);

namespace App\Application\UseCases\SalesOrder;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Models\SalesOrder;
use App\Domain\Repositories\SalesOrderRepositoryInterface;

final readonly class ShowSalesOrderUseCase
{
    public function __construct(
        private SalesOrderRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    public function execute(string $id): SalesOrder
    {
        $companyId = $this->companyResolver->resolve();

        return $this->repository->findForCompanyOrFail($id, $companyId);
    }
}
