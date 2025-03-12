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
     * @return Tank
     */
    public function create(array $data): Tank;

    /**
     * Update an existing tank record.
     *
     * @param string $id
     * @param array<string, mixed> $data
     * @return Tank|null
     */
    public function update(string $id, array $data): ?Tank;

    /**
     * Delete a tank record.
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool;

    /**
     * Paginate tank records.
     *
     * @param int $page
     * @return PaginationInterface
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a tank by a specific field.
     *
     * @param string $field
     * @param string|int $value
     * @return Tank|null
     */
    public function showTank(string $field, string | int $value): ?Tank;
}
