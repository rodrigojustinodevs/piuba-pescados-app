<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\SupplierDTO;
use App\Application\UseCases\Supplier\CreateSupplierUseCase;
use App\Application\UseCases\Supplier\DeleteSupplierUseCase;
use App\Application\UseCases\Supplier\ListSuppliersUseCase;
use App\Application\UseCases\Supplier\ShowSupplierUseCase;
use App\Application\UseCases\Supplier\UpdateSupplierUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SupplierService
{
    public function __construct(
        protected CreateSupplierUseCase $createSupplierUseCase,
        protected ListSuppliersUseCase $listSuppliersUseCase,
        protected ShowSupplierUseCase $showSupplierUseCase,
        protected UpdateSupplierUseCase $updateSupplierUseCase,
        protected DeleteSupplierUseCase $deleteSupplierUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): SupplierDTO
    {
        return $this->createSupplierUseCase->execute($data);
    }

    public function showAllSuppliers(): AnonymousResourceCollection
    {
        return $this->listSuppliersUseCase->execute();
    }

    public function showSupplier(string $id): ?SupplierDTO
    {
        return $this->showSupplierUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateSupplier(string $id, array $data): SupplierDTO
    {
        return $this->updateSupplierUseCase->execute($id, $data);
    }

    public function deleteSupplier(string $id): bool
    {
        return $this->deleteSupplierUseCase->execute($id);
    }
}
