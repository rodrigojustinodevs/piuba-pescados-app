<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Enums\PermissionsEnum;
use App\Domain\Enums\RolesEnum;
use App\Domain\Models\User;
use App\Domain\ValueObjects\Role;
use App\Domain\ValueObjects\TenantContext;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\DB;

/**
 * Resolve the effective permissions of a user in a company.
 *
 * Estratégia:
 *  1. Fetches the role of the user in the company (pivot company_user.role)
 *  2. Maps role → default permissions (PermissionsEnum::forRole)
 *  3. Applies individual overrides (user_company_permissions)
 *  4. Caches the result by user+company for 30 minutes
 *
 * Cache key: "perms:{userId}:{companyId}"
 */
final readonly class PermissionResolver
{
    private const int CACHE_TTL_SECONDS = 1800; // 30 min
    private const string CACHE_PREFIX      = 'perms';

    public function __construct(
        private CacheRepository $cache,
    ) {
    }

    /**
     * Builds the complete TenantContext for the user in the company.
     *
     * @throws \DomainException se o usuário não pertencer à empresa.
     */
    public function resolve(User $user, string $companyId): TenantContext
    {
        $cacheKey = $this->cacheKey((string) $user->id, $companyId);

        $data = $this->cache->remember(
            $cacheKey,
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->buildPermissionData($user, $companyId),
        );

        return new TenantContext(
            userId:      (string) $user->id,
            companyId:   $companyId,
            role:        new Role(RolesEnum::from($data['role'])),
            permissions: $data['permissions'],
        );
    }

    /**
     * Invalidates the cache of permissions for a user in a company.
     * Should be called when changing role/permissions.
     */
    public function invalidate(string $userId, string $companyId): void
    {
        $this->cache->forget($this->cacheKey($userId, $companyId));
    }

    /** Invalidates all caches of permissions for a user. */
    public function invalidateAll(string $userId): void
    {
        // For broad invalidation, it is recommended to use Redis with pattern delete.
        // Simple implementation with cache tag:
        $this->cache->tags(["user-perms:{$userId}"])->flush();
    }

    public function hasPermission(User $user, string $companyId, string $permission): bool
    {
        // master_admin passa sempre
        $pivot = DB::table('company_user')
            ->where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->value('role');

        if ($pivot && RolesEnum::from($pivot) === RolesEnum::MASTER_ADMIN) {
            return true;
        }

        $context = $this->resolve($user, $companyId);

        return in_array($permission, $context->permissions, strict: true);
    }

    // ─── Privados ─────────────────────────────────────────────────────────────

    /**
     * @return array{role: string, permissions: array<string>}
     * @throws \DomainException
     */
    private function buildPermissionData(User $user, string $companyId): array
    {
        // 1. Fetches the role of the user in the company
        $pivot = DB::table('company_user')
            ->where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->select(['role'])
            ->first();

        if (! $pivot) {
            throw new \DomainException(
                "User {$user->id} does not belong to company {$companyId} or is inactive."
            );
        }

        $role = RolesEnum::from($pivot->role);

        // 2. Default permissions for the role
        $permissions = collect(PermissionsEnum::forRole($role))
            ->map(fn (PermissionsEnum $p): string => $p->value)
            ->toArray();

        // 3. Applies individual overrides (grant/deny)
        $overrides = DB::table('user_company_permissions as ucp')
            ->join('permissions as p', 'p.id', '=', 'ucp.permission_id')
            ->where('ucp.user_id', $user->id)
            ->where('ucp.company_id', $companyId)
            ->select(['p.name', 'ucp.type'])
            ->get();

        foreach ($overrides as $override) {
            if ($override->type === 'grant') {
                if (! in_array($override->name, $permissions, true)) {
                    $permissions[] = $override->name;
                }
            } elseif ($override->type === 'deny') {
                $permissions = array_filter(
                    $permissions,
                    fn (string $p): bool => $p !== $override->name,
                );
            }
        }

        return [
            'role'        => $role->value,
            'permissions' => array_values($permissions),
        ];
    }

    private function cacheKey(string $userId, string $companyId): string
    {
        return self::CACHE_PREFIX . ":{$userId}:{$companyId}";
    }
}
