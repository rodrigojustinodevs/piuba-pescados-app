<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Stocking;

interface StockingRepositoryInterface
{
    /**
     * Create a new stocking record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Stocking;

    /**
     * Update an existing stocking record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Stocking;

    /**
     * Delete a stocking record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate stocking records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a stocking by a specific field.
     */
    public function showStocking(string $field, string | int $value): ?Stocking;

    /**
     * Adds amounts to accumulated_fixed_cost for multiple stockings in ONE query.
     *
     * @param array<string, float> $amountsByStockingId  stocking_id → amount to add
     */
    public function bulkIncrementFixedCost(array $amountsByStockingId): void;

    /**
     * Subtracts amounts from accumulated_fixed_cost for multiple stockings in ONE query.
     * The column is floored at 0 to prevent negative values caused by floating-point drift.
     *
     * @param array<string, float> $amountsByStockingId  stocking_id → amount to subtract
     */
    public function bulkDecrementFixedCost(array $amountsByStockingId): void;
}
