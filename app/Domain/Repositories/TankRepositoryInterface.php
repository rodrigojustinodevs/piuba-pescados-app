<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Tank;

interface TankRepositoryInterface
{
    /**
     * Create a new tank record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Tank;

    /**
     * Update an existing tank record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Tank;

    /**
     * Delete a tank record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate tank records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a tank by a specific field.
     */
    public function showTank(string $field, string | int $value): ?Tank;
}
