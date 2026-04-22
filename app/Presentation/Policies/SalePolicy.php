<?php

declare(strict_types=1);

namespace App\Presentation\Policies;

use App\Domain\Enums\PermissionsEnum;
use App\Domain\Enums\RolesEnum;
use App\Domain\Models\Sale;
use App\Domain\Models\User;
use App\Domain\ValueObjects\TenantContext;

// ═════════════════════════════════════════════════════════════════════════════
// SalePolicy
// ═════════════════════════════════════════════════════════════════════════════
final class SalePolicy
{
    public function viewAny(): bool
    {
        return $this->context()->hasPermission(PermissionsEnum::VIEW_SALE);
    }

    public function view(User $user, Sale $sale): bool
    {
        if (! $this->context()->hasPermission(PermissionsEnum::VIEW_SALE)) {
            return false;
        }

        $this->context()->assertOwns($sale->company_id);

        return true;
    }

    public function create(): bool
    {
        return $this->context()->hasPermission(PermissionsEnum::CREATE_SALE);
    }

    public function update(User $user, Sale $sale): bool
    {
        if (! $this->context()->hasPermission(PermissionsEnum::EDIT_SALE)) {
            return false;
        }

        $this->context()->assertOwns($sale->company_id);

        return true;
    }

    public function delete(User $user, Sale $sale): bool
    {
        if (! $this->context()->hasPermission(PermissionsEnum::DELETE_SALE)) {
            return false;
        }

        if (! $this->context()->isAtLeastRole(RolesEnum::COMPANY_ADMIN)) {
            return false;
        }

        $this->context()->assertOwns($sale->company_id);

        return true;
    }

    public function approve(): bool
    {
        return $this->context()->hasPermission(PermissionsEnum::APPROVE_SALE)
            && $this->context()->isAtLeastRole(RolesEnum::MANAGER);
    }

    public function cancel(): bool
    {
        return $this->context()->hasPermission(PermissionsEnum::CANCEL_SALE)
            && $this->context()->isAtLeastRole(RolesEnum::MANAGER);
    }

    private function context(): TenantContext
    {
        return app(TenantContext::class);
    }
}
