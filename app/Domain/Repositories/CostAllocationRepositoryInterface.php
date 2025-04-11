<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\CostAllocation;

interface CostAllocationRepositoryInterface
{
    /**
     * Create a new costAllocation record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): CostAllocation;

    /**
     * Update an existing costAllocation record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?CostAllocation;

    /**
     * Delete a costAllocation record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate costAllocation records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a costAllocation by a specific field.
     */
    public function showCostAllocation(string $field, string | int $value): ?CostAllocation;
}
