<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\SupplierInputDTO;
use App\Domain\Models\Supplier;

interface SupplierRepositoryInterface
{
    /**
     * @param array{
     *     company_id?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    public function findOrFail(string $id): Supplier;

    public function showSupplier(string $field, string | int $value): ?Supplier;

    public function create(SupplierInputDTO $dto): Supplier;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Supplier;

    public function delete(string $id): bool;
}
