<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Traits;

use App\Domain\Enums\PermissionsEnum;
use App\Domain\ValueObjects\TenantContext;

/**
 * Trait added to User for quick permission checks.
 * The actual permissions are resolved by the PermissionResolver (with cache).
 */
trait HasPermissions
{
    /**
     * Checks if the user has the permission in the active context.
     * Requires TenantContext to be bound in the container.
     *
     * @param string|iterable<int, string> $abilities
     * @param mixed $arguments
     */
    public function can($abilities, $arguments = []): bool
    {
        // Delegates to the Laravel Gate (which uses the Policies and the context)
        return app(\Illuminate\Contracts\Auth\Access\Gate::class)->allows($abilities, $arguments);
    }

    /** Checks permission directly in the TenantContext. */
    public function hasPermission(string | PermissionsEnum $permission): bool
    {
        if (! app()->bound(TenantContext::class)) {
            return false;
        }

        return app(TenantContext::class)->hasPermission($permission);
    }

    /**
     * @param list<string|PermissionsEnum> $permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return collect($permissions)->some(
            fn (string | PermissionsEnum $p): bool => $this->hasPermission($p)
        );
    }

    /**
     * @param list<string|PermissionsEnum> $permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        return collect($permissions)->every(
            fn (string | PermissionsEnum $p): bool => $this->hasPermission($p)
        );
    }
}
