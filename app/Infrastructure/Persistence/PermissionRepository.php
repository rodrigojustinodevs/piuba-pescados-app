<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Permission;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\PermissionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class PermissionRepository implements PermissionRepositoryInterface
{
    /**
     * Create a new permission.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    /**
     * Update an existing permission.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Permission
    {
        $permission = Permission::find($id);

        if ($permission) {
            $permission->update($data);

            return $permission;
        }

        return null;
    }

    /**
     * Get paginated companies.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<Permission> $paginator */
        $paginator = Permission::paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show permission by field and value.
     */
    public function showPermission(string $field, string | int $value): ?Permission
    {
        return Permission::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $permission = Permission::find($id);

        if (! $permission) {
            return false;
        }

        return (bool) $permission->delete();
    }
}
