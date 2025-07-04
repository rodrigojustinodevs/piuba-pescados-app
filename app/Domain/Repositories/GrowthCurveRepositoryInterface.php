<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\GrowthCurve;

interface GrowthCurveRepositoryInterface
{
    /**
     * Create a new GrowthCurve record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): GrowthCurve;

    /**
     * Update an existing GrowthCurve record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?GrowthCurve;

    /**
     * Delete a GrowthCurve record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate GrowthCurve records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a GrowthCurve by a specific field.
     */
    public function showGrowthCurve(string $field, string | int $value): ?GrowthCurve;
}
