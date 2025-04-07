<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\WaterQuality;

interface WaterQualityRepositoryInterface
{
    /**
     * Create a new waterQuality record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): WaterQuality;

    /**
     * Update an existing waterQuality record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?WaterQuality;

    /**
     * Delete a waterQuality record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate waterQuality records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a waterQuality by a specific field.
     */
    public function showWaterQuality(string $field, string | int $value): ?WaterQuality;
}
