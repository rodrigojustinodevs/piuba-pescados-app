<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\PurchaseRepositoryInterface;

final readonly class ListPurchasesUseCase
{
    public function __construct(
        private PurchaseRepositoryInterface $purchaseRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array{
     *     status?: string|null,
     *     paymentStatus?: string|null,
     *     paymentMethod?: string|null,
     *     supplierId?: string|null,
     *     code?: string|null,
     *     dateFrom?: string|null,
     *     dateTo?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['companyId'] = $this->companyResolver->resolve();

        return $this->purchaseRepository->paginate($filters);
    }
}
