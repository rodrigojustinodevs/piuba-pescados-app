<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Domain\Enums\RolesEnum;
use App\Domain\Models\User;
use App\Infrastructure\Security\PermissionResolver;
use Illuminate\Support\Facades\DB;

final readonly class AssignUserToCompanyUseCase
{
    public function __construct(
        private PermissionResolver $permissionResolver,
    ) {
    }

    /**
     * @throws \DomainException Em caso de violação de regras de negócio.
     */
    public function execute(
        User $targetUser,
        string $companyId,
        RolesEnum $role,
        User $actingUser, // who is executing the action
    ): void {
        // 1. Valida contexto do usuário executor
        $actorContext = $this->permissionResolver->resolve($actingUser, $companyId);
        $actorContext->assertOwns($companyId);

        // 2. Regra de negócio: ninguém pode atribuir role maior que o próprio
        if ($role->level() >= $actorContext->role->enum->level() && ! $actorContext->isGlobal()) {
            throw new \DomainException(
                "Você não pode atribuir o role '{$role->label()}' — é superior ao seu."
            );
        }

        // 3. Upsert no pivot (idempotente)
        DB::table('company_user')->upsert(
            [
                'user_id'    => $targetUser->id,
                'company_id' => $companyId,
                'role'       => $role->value,
                'is_active'  => true,
                'joined_at'  => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            uniqueBy: ['user_id', 'company_id'],
            update: ['role', 'is_active', 'updated_at'],
        );

        // 4. Invalida cache de permissões do usuário afetado
        $this->permissionResolver->invalidate($targetUser->id, $companyId);
    }
}
