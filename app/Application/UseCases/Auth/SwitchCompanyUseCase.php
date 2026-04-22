<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Domain\Models\User;
use App\Infrastructure\Security\CompanyJwtService;
use App\Infrastructure\Security\PermissionResolver;

// ═════════════════════════════════════════════════════════════════════════════
// USE CASE: SwitchCompanyUseCase
// Permite ao usuário autenticado trocar a empresa ativa e receber novo token.
// ═════════════════════════════════════════════════════════════════════════════

final readonly class SwitchCompanyUseCase
{
    public function __construct(
        private PermissionResolver $permissionResolver,
        private CompanyJwtService $jwtService,
    ) {
    }

    /**
     * @return array{token: string, company_id: string, role: string, expires_in: int}
     * @throws \DomainException Se o usuário não pertencer à empresa.
     */
    public function execute(User $user, string $companyId): array
    {
        // 1. Valida que o usuário pertence à empresa
        //    A exceção é lançada dentro do resolver se não pertencer.
        $context = $this->permissionResolver->resolve($user, $companyId);

        // 2. Invalida o token atual (segurança: evita tokens duplos ativos)
        $this->jwtService->invalidateCurrentToken();

        // 3. Gera novo token com claims da nova empresa
        $token = $this->jwtService->generateToken($user, $context);

        return [
            'token'      => $token,
            'company_id' => $context->companyId,
            'role'       => $context->role->value(),
            'expires_in' => (int) config('jwt.ttl') * 60, // segundos
        ];
    }
}
