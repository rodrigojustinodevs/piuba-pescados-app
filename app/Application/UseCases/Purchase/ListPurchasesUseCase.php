<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;

final readonly class ListPurchasesUseCase
{
    public function __construct(
        private PurchaseRepositoryInterface $purchaseRepository,
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
        if (! CompanyContext::isMasterAdmin()) {
            $filters['companyId'] = CompanyContext::requireCompanyId();
        }

        return $this->purchaseRepository->paginate($filters);
    }
}
