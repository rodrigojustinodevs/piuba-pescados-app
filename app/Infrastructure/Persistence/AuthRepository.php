<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\LoginCredentialsDTO;
use App\Domain\Models\User;
use App\Domain\Repositories\AuthRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthRepository implements AuthRepositoryInterface
{
    public function attemptLogin(LoginCredentialsDTO $credentials): ?string
    {
        try {
            /** @var string|false $token */
            $token = auth('api')->attempt([
                'email'    => $credentials->email,
                'password' => $credentials->password,
            ]);

            return $token !== false ? $token : null;
        } catch (JWTException) {
            return null;
        }
    }

    public function userHasRole(string $role): bool
    {
        $roleArray = explode('|', $role);

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user
            ->roles()
            ->whereIn('name', $roleArray)
            ->exists();
    }

    public function userHasPermission(string $permission): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->roles()->where('name', 'master_admin')->exists()) {
            return true;
        }

        if (
            $user
                ->permissions()
                ->where('name', $permission)
                ->exists()
        ) {
            return true;
        }

        return (bool) $user
            ->roles()
            ->whereHas('permissions', fn ($q) => $q->where('name', $permission))
            ->exists();
    }

    public function isMasterAdmin(User $user): bool
    {
        return DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->where('roles.name', 'master_admin')
            ->exists();
    }

    /**
     * @return Collection<int, string>
     */
    public function getAllPermissions(): Collection
    {
        return DB::table('permissions')->pluck('name');
    }

    /**
     * @return Collection<int, string>
     */
    public function getUserDirectPermissions(User $user): Collection
    {
        return DB::table('permission_user')
            ->select('permissions.name')
            ->join('permissions', 'permissions.id', '=', 'permission_user.permission_id')
            ->where('permission_user.user_id', $user->id)
            ->pluck('name');
    }

    /**
     * @return Collection<int, string>
     */
    public function getUserDirectPermissionsByCompany(User $user, string $companyId): Collection
    {
        return DB::table('company_user_permission')
            ->select('permissions.name')
            ->join('permissions', 'permissions.id', '=', 'company_user_permission.permission_id')
            ->where('company_user_permission.user_id', $user->id)
            ->where('company_user_permission.company_id', $companyId)
            ->pluck('name');
    }

    /**
     * @return Collection<int, string>
     */
    public function getUserRolePermissions(User $user): Collection
    {
        return DB::table('role_user')
            ->select('permissions.name')
            ->join('permission_role', 'permission_role.role_id', '=', 'role_user.role_id')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('role_user.user_id', $user->id)
            ->pluck('name');
    }

    /**
     * @return Collection<int, string>
     */
    public function getUserRolePermissionsByCompany(User $user, string $companyId): Collection
    {
        return DB::table('company_user_role')
            ->select('permissions.name')
            ->join('permission_role', 'permission_role.role_id', '=', 'company_user_role.role_id')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('company_user_role.user_id', $user->id)
            ->where('company_user_role.company_id', $companyId)
            ->pluck('name');
    }
}
