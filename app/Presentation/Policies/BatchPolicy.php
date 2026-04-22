<?php

declare(strict_types=1);

namespace App\Presentation\Policies;

use App\Domain\Enums\PermissionsEnum;
use App\Domain\Enums\RolesEnum;
use App\Domain\Models\Batch;
use App\Domain\Models\User;
use App\Domain\ValueObjects\TenantContext;

final class BatchPolicy
{
    public function viewAny(): bool
    {
        return $this->context()->hasPermission(PermissionsEnum::VIEW_BATCH);
    }

    public function view(User $user, Batch $batch): bool
    {
        if (! $this->context()->hasPermission(PermissionsEnum::VIEW_BATCH)) {
            return false;
        }

        return $this->assertOwnership($batch);
    }

    public function create(): bool
    {
        return $this->context()->hasPermission(PermissionsEnum::CREATE_BATCH);
    }

    public function update(User $user, Batch $batch): bool
    {
        if (! $this->context()->hasPermission(PermissionsEnum::UPDATE_BATCH)) {
            return false;
        }

        return $this->assertOwnership($batch);
    }

    public function delete(User $user, Batch $batch): bool
    {
        if (! $this->context()->hasPermission(PermissionsEnum::DELETE_BATCH)) {
            return false;
        }

        if (! $this->context()->isAtLeastRole(RolesEnum::COMPANY_ADMIN)) {
            return false;
        }

        return $this->assertOwnership($batch);
    }

    public function finish(User $user, Batch $batch): bool
    {
        if (! $this->context()->hasPermission(PermissionsEnum::UPDATE_BATCH)) {
            return false;
        }

        return $this->assertOwnership($batch);
    }

    public function distribution(): bool
    {
        return $this->context()->hasPermission(PermissionsEnum::CREATE_BATCH);
    }

    private function assertOwnership(Batch $batch): bool
    {
        $companyId = $batch->tank?->company_id;

        if (! is_string($companyId) || $companyId === '') {
            return false;
        }

        $this->context()->assertOwns($companyId);

        return true;
    }

    private function context(): TenantContext
    {
        return app(TenantContext::class);
    }
}
