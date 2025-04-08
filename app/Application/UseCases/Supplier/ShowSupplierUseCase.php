<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supplier;

use App\Application\DTOs\SupplierDTO;
use App\Domain\Models\Supplier;
use App\Domain\Repositories\SupplierRepositoryInterface;
use RuntimeException;

class ShowSupplierUseCase
{
    public function __construct(
        protected SupplierRepositoryInterface $supplierRepository
    ) {
    }

    public function execute(string $id): ?SupplierDTO
    {
        $supplier = $this->supplierRepository->showSupplier('id', $id);

        if (! $supplier instanceof Supplier) {
            throw new RuntimeException('Supplier not found');
        }

        return new SupplierDTO(
            id: $supplier->id,
            name: $supplier->name,
            contact: $supplier->contact,
            phone: $supplier->phone,
            email: $supplier->email,
            company: [
                'name' => $supplier->company->name ?? '',
            ],
            createdAt: $supplier->created_at?->toDateTimeString(),
            updatedAt: $supplier->updated_at?->toDateTimeString()
        );
    }
}
