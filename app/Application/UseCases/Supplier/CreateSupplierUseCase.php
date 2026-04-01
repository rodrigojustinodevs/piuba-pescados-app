<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supplier;

use App\Application\DTOs\SupplierInputDTO;
use App\Domain\Models\Supplier;
use App\Domain\Repositories\SupplierRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateSupplierUseCase
{
    public function __construct(
        private SupplierRepositoryInterface $supplierRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Supplier
    {
        return DB::transaction(function () use ($data): Supplier {
            $dto = SupplierInputDTO::fromArray($data);

            return $this->supplierRepository->create($dto);
        });
    }
}
