<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supplier;

use App\Application\DTOs\SupplierDTO;
use App\Domain\Repositories\SupplierRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateSupplierUseCase
{
    public function __construct(
        protected SupplierRepositoryInterface $supplierRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): SupplierDTO
    {
        return DB::transaction(function () use ($data): SupplierDTO {
            $supplier = $this->supplierRepository->create($data);

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
        });
    }
}
