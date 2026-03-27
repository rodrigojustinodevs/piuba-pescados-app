<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supplier;

use App\Domain\Repositories\SupplierRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteSupplierUseCase
{
    public function __construct(
        private SupplierRepositoryInterface $supplierRepository,
    ) {
    }

    public function execute(string $id): void
    {
        DB::transaction(function () use ($id): void {
            $this->supplierRepository->delete($id);
        });
    }
}
