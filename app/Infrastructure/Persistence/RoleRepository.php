<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Role;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\RoleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleRepository implements RoleRepositoryInterface
{
    /**
     * Create a new role.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Role
    {
        return Role::create($data);
    }

    /**
     * Update an existing role.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Role
    {
        $role = Role::find($id);

        if ($role) {
            $role->update($data);

            return $role;
        }

        return null;
    }

    /**
     * Get paginated companies.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<Role> $paginator */
        $paginator = Role::paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show role by field and value.
     */
    public function showRole(string $field, string | int $value): ?Role
    {
        return Role::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $role = Role::find($id);

        if (! $role) {
            return false;
        }

        return (bool) $role->delete();
    }
}
