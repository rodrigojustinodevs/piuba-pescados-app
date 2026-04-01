<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supplier;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SupplierRepositoryInterface;

final readonly class ListSuppliersUseCase
{
    public function __construct(
        private SupplierRepositoryInterface $supplierRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        $filters['company_id'] = $this->companyResolver->resolve();

        return $this->supplierRepository->paginate($filters);
    }
}
