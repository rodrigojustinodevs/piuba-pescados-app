<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Permission;

interface PermissionRepositoryInterface
{
    /**
     * Create a new permission record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Permission;

    /**
     * Update an existing permission record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Permission;

    /**
     * Delete a permission record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate permission records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a permission by a specific field.
     */
    public function showPermission(string $field, string | int $value): ?Permission;
}
