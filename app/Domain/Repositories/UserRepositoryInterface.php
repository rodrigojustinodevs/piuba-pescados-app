<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\User;

interface UserRepositoryInterface
{
    /**
     * Create a new user record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): User;

    /**
     * Update an existing user record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?User;

    /**
     * Delete a user record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate user records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a user by a specific field.
     */
    public function showUser(string $field, string | int $value): ?User;
}
