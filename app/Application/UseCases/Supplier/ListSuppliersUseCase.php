<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supplier;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SupplierRepositoryInterface;

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
        return $this->supplierRepository->paginate($filters);
    }
}
