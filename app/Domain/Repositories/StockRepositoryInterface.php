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

    /**
     * Find first stock by company and supplier.
     */
    public function findByCompanyAndSupplier(string $companyId, string $supplierId): ?Stock;

    /**
     * Find stocks by supplier ID.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Stock>
     */
    public function findBySupplier(string $supplierId): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get unit price for a stock by ID.
     */
    public function getUnitPriceByStockId(string $stockId): float;

    /**
     * Decrement the stock quantity.
     */
    public function decrementStock(string $id, float $quantity): bool;

    /**
     * Increment the stock quantity (e.g. revert part of a feeding update).
     */
    public function incrementStock(string $id, float $quantity): bool;
}
