<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Domain\Enums\RolesEnum;
use App\Domain\Models\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;
use App\Infrastructure\Security\PermissionResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class AssignUserToCompanyUseCase
{
    public function __construct(
        private PermissionResolver $permissionResolver,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @throws \DomainException Em caso de violação de regras de negócio.
     */
    public function execute(
        string $targetUserId,
        ?string $requestedCompanyId,
        RolesEnum $role,
        User $actingUser, // who is executing the action
    ): User {

        $targetUser = $this->userRepository->findOrFail($targetUserId);

        $companyId  = CompanyContext::resolveTargetCompanyId($requestedCompanyId);

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
        DB::transaction(function () use ($targetUser, $companyId, $role): void {
            DB::table('company_user')->upsert(
                [
                    'id'         => (string) Str::uuid(),
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
        });

        // 4. Invalida cache de permissões do usuário afetado
        $this->permissionResolver->invalidate($targetUser->id, $companyId);

        return $targetUser->refresh()->load([
            'companyMemberships' => fn ($q) => $q->where('company_id', $companyId)->with('company'),
        ]);
    }
}
