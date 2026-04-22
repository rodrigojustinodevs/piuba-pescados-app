<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Enums\PermissionsEnum;
use App\Domain\Enums\RolesEnum;

/**
 * Value Object immutable that represents the active tenant context in the request.
 *
 * Created by the PermissionResolver and linked in the DI container by the middleware
 * CheckCompanyContext. Available throughout the request chain via:
 *
 *   app(TenantContext::class)
 *   $request->attributes->get('tenant_context')
 *
 * It is the only source of truth for:
 *   - What user is authenticated         → $context->userId
 *   - What company is active             → $context->companyId
 *   - What role the user has in it       → $context->role
 *   - What permissions the user has       → $context->permissions
 *
 * NEVER accepts company_id coming from the request — always extracted from the JWT.
 */
final readonly class TenantContext implements \Stringable
{
    /**
     * @param string          $userId      ID of the authenticated user
     * @param string          $companyId   ID of the active company (extracted from the `cid` claim of the JWT)
     * @param Role         $role        Role of the user in the active company
     * @param list<string> $permissions List of effective permission values (role + overrides)
     */
    public function __construct(
        public string $userId,
        public string $companyId,
        public Role $role,
        public array $permissions,
    ) {
    }

    // ─── Verification of Permissions ───────────────────────────────────────────
    /**
     * Checks if the context has a specific permission.
     *
     * master_admin sempre retorna true (bypass global).
     */
    public function hasPermission(string | PermissionsEnum $permission): bool
    {
        // master_admin bypasses any verification
        if ($this->isGlobal()) {
            return true;
        }
        $value = $permission instanceof PermissionsEnum
            ? $permission->value
            : $permission;

        return in_array($value, $this->permissions, strict: true);
    }

    /**
     * Checks if the context has ANY of the permissions (OR).
     *
     * @param list<string|PermissionsEnum> $permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isGlobal()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the context has ALL of the permissions (AND).
     *
     * @param list<string|PermissionsEnum> $permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->isGlobal()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (! $this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    // ─── Verification de Role ──────────────────────────────────────────────────

    /**
     * Checks if the role of the context is at least the role informed.
     * Usa a hierarquia numérica definida em RolesEnum::level().
     *
     * Example:
     *   $context->role = MANAGER (level 2)
     *   $context->isAtLeastRole(RolesEnum::OPERATOR) → true
     *   $context->isAtLeastRole(RolesEnum::ADMIN)    → false
     */
    public function isAtLeastRole(RolesEnum $required): bool
    {
        return $this->role->isAtLeast($required);
    }

    /**
     * Checks if the role is exactly the one informed.
     */
    public function hasRole(RolesEnum $role): bool
    {
        return $this->role->enum === $role;
    }

    /**
     * Checks if it is master_admin — global access without company restriction.
     */
    public function isGlobal(): bool
    {
        return $this->role->isGlobal();
    }

    // ─── Validation of Tenant ──────────────────────────────────────────────────

    /**
     * Ensures that the informed company_id belongs to the active context.
     *
     * Must be called whenever a business entity is accessed
     * and we want to ensure the tenant isolation.
     *
     * master_admin is immune (global access).
     *
     * @throws \DomainException if the company_id does not correspond to the context
     */
    public function assertOwns(string $companyId): void
    {
        if ($this->isGlobal()) {
            return;
        }

        if ($this->companyId !== $companyId) {
            throw new \DomainException(
                "Access denied: operation restricted to company #{$this->companyId}. " .
                "Attempt to access company #{$companyId}."
            );
        }
    }

    /**
     * Boolean version of assertOwns() — does not throw an exception.
     */
    public function owns(string $companyId): bool
    {
        if ($this->isGlobal()) {
            return true;
        }
        return $this->companyId === $companyId;
    }

    // ─── Serialization / Helpers ───────────────────────────────────────────────

    /**
     * Returns an array representation (useful for logs and responses).
     *
     * @return array{
     *     user_id: string,
     *     company_id: string,
     *     role: string,
     *     is_global: bool,
     *     permissions_count: int
     * }
     */
    public function toArray(): array
    {
        return [
            'user_id'           => $this->userId,
            'company_id'        => $this->companyId,
            'role'              => $this->role->value(),
            'is_global'         => $this->isGlobal(),
            'permissions_count' => count($this->permissions),
        ];
    }

    /**
     * Returns the complete array including the list of permissions.
     * Use with caution — may expose sensitive data in logs.
     *
     * @return array{
     *     user_id: string,
     *     company_id: string,
     *     role: string,
     *     is_global: bool,
     *     permissions: list<string>
     * }
     */
    public function toFullArray(): array
    {
        return [
            'user_id'     => $this->userId,
            'company_id'  => $this->companyId,
            'role'        => $this->role->value(),
            'is_global'   => $this->isGlobal(),
            'permissions' => $this->permissions,
        ];
    }

    public function __toString(): string
    {
        return sprintf(
            'TenantContext[user=%s, company=%s, role=%s]',
            $this->userId,
            $this->companyId,
            $this->role->value(),
        );
    }
}
