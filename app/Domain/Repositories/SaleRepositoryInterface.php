<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Sale;

interface SaleRepositoryInterface
{
    /**
     * Create a new Sale record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Sale;

    /**
     * Update an existing Sale record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Sale;

    /**
     * Delete a Sale record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate Sale records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a Sale by a specific field.
     */
    public function showSale(string $field, string | int $value): ?Sale;
}
