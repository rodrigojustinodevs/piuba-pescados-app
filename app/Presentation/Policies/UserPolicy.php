<?php

declare(strict_types=1);

namespace App\Presentation\Policies;

use App\Domain\Enums\PermissionsEnum;
use App\Domain\Enums\RolesEnum;
use App\Domain\Models\User;
use App\Domain\ValueObjects\TenantContext;

final class UserPolicy
{
    public function viewAny(): bool
    {
        return $this->context()->hasPermission(PermissionsEnum::VIEW_USER);
    }

    public function view(User $actingUser, User $targetUser): bool
    {
        if (! $this->context()->hasPermission(PermissionsEnum::VIEW_USER)) {
            return false;
        }

        if ($actingUser->id === $targetUser->id) {
            return true;
        }

        return $targetUser->belongsToCompany($this->context()->companyId);
    }

    public function create(): bool
    {
        return $this->context()->hasPermission(PermissionsEnum::CREATE_USER);
    }

    public function update(User $actingUser, User $targetUser): bool
    {
        if (! $this->context()->hasPermission(PermissionsEnum::EDIT_USER)) {
            return false;
        }

        $targetRole = $targetUser->roleInCompany($this->context()->companyId);

        if ($targetRole && $targetRole->level() >= $this->context()->role->enum->level()) {
            return $this->context()->isGlobal();
        }

        return $targetUser->belongsToCompany($this->context()->companyId);
    }

    public function delete(User $actingUser, User $targetUser): bool
    {
        if (! $this->context()->hasPermission(PermissionsEnum::DELETE_USER)) {
            return false;
        }

        if ($actingUser->id === $targetUser->id) {
            return false;
        }

        return $this->context()->isAtLeastRole(RolesEnum::COMPANY_ADMIN);
    }

    public function assignRole(): bool
    {
        return $this->context()->hasPermission(PermissionsEnum::ASSIGN_USER_ROLE)
            && $this->context()->isAtLeastRole(RolesEnum::ADMIN);
    }

    private function context(): TenantContext
    {
        return app(TenantContext::class);
    }
}
