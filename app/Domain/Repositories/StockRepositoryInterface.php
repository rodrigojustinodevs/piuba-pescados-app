<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Stock;

interface StockRepositoryInterface
{
    /**
     * Create a new stock record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Stock;

    /**
     * Update an existing feeding record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Stock;

    /**
     * Delete a stock record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate stock records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a stock by a specific field.
     */
    public function showStock(string $field, string | int $value): ?Stock;
}
