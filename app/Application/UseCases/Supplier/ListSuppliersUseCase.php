<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supplier;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SupplierRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;

final readonly class ListSuppliersUseCase
{
    public function __construct(
        private SupplierRepositoryInterface $supplierRepository,
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

        return $this->supplierRepository->paginate($filters);
    }
}
