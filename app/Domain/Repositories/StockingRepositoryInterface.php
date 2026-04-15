<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\StockingInputDTO;
use App\Domain\Models\Stocking;

interface StockingRepositoryInterface
{
    /**
     * @param array{
     *     batch_id?: string|null,
     *     company_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     status?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    /**
     * Find a stocking by ID.
     */
    public function findOrFail(string $id): Stocking;

    /**
     * Create a new stocking record.
     */
    public function create(StockingInputDTO $dto): Stocking;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Stocking;

    /**
     * Delete a stocking record.
     */
    public function delete(string $id): bool;

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

    /**
     * Find a stocking by company ID.
     */
    public function findByCompanyOrFail(string $stockingId, string $companyId): Stocking;

    /**
     * Find a stocking by batch ID.
     */
    public function findByBatchOrFail(string $batchId): ?Stocking;

    /**
     * Check if there are any active stockings in a batch.
     */
    public function hasActiveStockingsInBatch(string $batchId, ?string $excludeStockingId = null): bool;

    /**
     * Get the total accumulated cost of a batch.
     */
    public function totalAccumulatedCost(string $stockingId): float;

    /**
     * Busca o stocking aplicando lockForUpdate (lock pessimista).
     * Usado pelo UpdateSaleUseCase para evitar edições concorrentes no mesmo stocking.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFailLocked(string $id): Stocking;
}
