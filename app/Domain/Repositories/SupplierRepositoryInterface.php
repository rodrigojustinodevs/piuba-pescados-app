<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Supplier;

interface SupplierRepositoryInterface
{
    /**
     * Create a new supplier record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Supplier;

    /**
     * Update an existing supplier record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Supplier;

    /**
     * Delete a supplier record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate supplier records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a supplier by a specific field.
     */
    public function showSupplier(string $field, string | int $value): ?Supplier;
}
