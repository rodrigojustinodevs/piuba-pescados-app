<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Harvest;

interface HarvestRepositoryInterface
{
    /**
     * Create a new harvest record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Harvest;

    /**
     * Update an existing harvest record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Harvest;

    /**
     * Delete a harvest record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate harvest records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a harvest by a specific field.
     */
    public function showHarvest(string $field, string | int $value): ?Harvest;
}
