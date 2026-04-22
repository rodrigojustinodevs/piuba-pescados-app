<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Domain\Enums\PermissionsEnum;
use App\Infrastructure\Security\PermissionResolver;
use Illuminate\Support\Facades\DB;

final readonly class RevokeUserCompanyPermissionUseCase
{
    public function __construct(
        private PermissionResolver $permissionResolver,
    ) {
    }

    public function execute(string $userId, string $companyId, PermissionsEnum $permission): void
    {
        $permId = DB::table('permissions')
            ->where('name', $permission->value)
            ->value('id');

        if (! $permId) {
            throw new \InvalidArgumentException("Permission '{$permission->value}' não encontrada.");
        }

        DB::table('user_company_permissions')->upsert(
            [
                'user_id'       => $userId,
                'company_id'    => $companyId,
                'permission_id' => $permId,
                'type'          => 'deny',
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            uniqueBy: ['user_id', 'company_id', 'permission_id'],
            update: ['type', 'updated_at'],
        );

        $this->permissionResolver->invalidate($userId, $companyId);
    }
}
