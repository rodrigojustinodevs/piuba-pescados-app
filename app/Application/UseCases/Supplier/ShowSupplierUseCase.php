<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supplier;

use App\Domain\Models\Supplier;
use App\Domain\Repositories\SupplierRepositoryInterface;

final readonly class ShowSupplierUseCase
{
    public function __construct(
        private SupplierRepositoryInterface $supplierRepository,
    ) {
    }

    public function execute(string $id): Supplier
    {
        return $this->supplierRepository->findOrFail($id);
    }
}
