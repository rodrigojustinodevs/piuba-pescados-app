<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Role;

interface RoleRepositoryInterface
{
    /**
     * Create a new role record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Role;

    /**
     * Update an existing role record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Role;

    /**
     * Delete a role record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate role records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a role by a specific field.
     */
    public function showRole(string $field, string | int $value): ?Role;
}
