<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Application\UseCases\Auth\ResolveUserPermissionsUseCase;
use App\Domain\Models\User;

class CheckUserPermissionUseCase
{
    public function __construct(
        protected ResolveUserPermissionsUseCase $resolvePermissionsUseCase
    ) {
    }

    /**
     * Verifica se usuário tem permissão específica
     */
    public function execute(User $user, string $permission, ?string $companyId = null): bool
    {
        $permissions = $this->resolvePermissionsUseCase->execute($user, $companyId);

        return $permissions->contains($permission);
    }

    /**
     * Verifica se usuário tem qualquer uma das permissões
     *
     * @param array<string> $permissions
     */
    public function userHasAnyPermission(User $user, array $permissions, ?string $companyId = null): bool
    {
        $userPermissions = $this->resolvePermissionsUseCase->execute($user, $companyId);

        return $userPermissions->intersect($permissions)->isNotEmpty();
    }
}
