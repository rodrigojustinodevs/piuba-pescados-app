<?php

declare(strict_types=1);

namespace App\Presentation\Policies;

use App\Domain\Enums\PermissionsEnum;
use App\Domain\Enums\RolesEnum;
use App\Domain\Models\Sensor;
use App\Domain\Models\User;
use App\Domain\ValueObjects\TenantContext;
use App\Infrastructure\Security\CompanyContext;

final class SensorPolicy
{
    public function viewAny(): bool
    {
        return $this->context()->hasPermission(PermissionsEnum::VIEW_SENSOR);
    }

    public function view(User $user, Sensor $sensor): bool
    {
        if (! $this->context()->hasPermission(PermissionsEnum::VIEW_SENSOR)) {
            if (!CompanyContext::isMasterAdmin()) {
                return false;
            }
            return true;
        }

        $this->context()->assertOwns($sensor->company_id);

        return true;
    }

    public function create(): bool
    {
        return $this->context()->hasPermission(PermissionsEnum::CREATE_SENSOR);
    }

    public function update(User $user, Sensor $sensor): bool
    {
        if (! $this->context()->hasPermission(PermissionsEnum::UPDATE_SENSOR)) {
            return false;
        }

        $this->context()->assertOwns($sensor->company_id);

        return true;
    }

    public function delete(User $user, Sensor $sensor): bool
    {
        if (! $this->context()->hasPermission(PermissionsEnum::DELETE_SENSOR)) {
            return false;
        }

        if (! $this->context()->isAtLeastRole(RolesEnum::COMPANY_ADMIN)) {
            return false;
        }

        $this->context()->assertOwns($sensor->company_id);

        return true;
    }

    private function context(): TenantContext
    {
        return app(TenantContext::class);
    }
}
