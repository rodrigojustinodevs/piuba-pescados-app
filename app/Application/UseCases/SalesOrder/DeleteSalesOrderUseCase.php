<?php

declare(strict_types=1);

namespace App\Application\UseCases\SalesOrder;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Exceptions\SalesOrderDeleteForbiddenException;
use App\Domain\Repositories\SalesOrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteSalesOrderUseCase
{
    public function __construct(
        private SalesOrderRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    public function execute(string $id): void
    {
        DB::transaction(function () use ($id): void {
            $companyId = $this->companyResolver->resolve();
            $order     = $this->repository->findForCompanyOrFail($id, $companyId);

            if (! $order->status->allowsQuotationEditing()) {
                throw SalesOrderDeleteForbiddenException::forStatus($order->status);
            }

            $order->delete();
        });
    }
}
