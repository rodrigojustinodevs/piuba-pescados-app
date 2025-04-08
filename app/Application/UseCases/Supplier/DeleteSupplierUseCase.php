<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supplier;

use App\Domain\Repositories\SupplierRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteSupplierUseCase
{
    public function __construct(
        protected SupplierRepositoryInterface $supplierRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->supplierRepository->delete($id));
    }
}
