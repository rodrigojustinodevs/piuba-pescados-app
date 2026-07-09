<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;
use App\Infrastructure\Security\PermissionResolver;
use Illuminate\Support\Facades\DB;

final readonly class DeleteUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PermissionResolver $permissionResolver,
    ) {
    }

    /**
     * Removes the target user's membership in a company — never deletes the
     * global account, since the same user may belong to other companies.
     *
     * @throws \DomainException se o usuário não pertencer à empresa resolvida.
     */
    public function execute(string $userId, ?string $requestedCompanyId): void
    {
        $companyId = CompanyContext::resolveTargetCompanyId($requestedCompanyId);
        $user      = $this->userRepository->findOrFail($userId);

        if (! $user->companies()->where('companies.id', $companyId)->exists()) {
            throw new \DomainException('Usuário não pertence a esta empresa.');
        }

        DB::transaction(function () use ($user, $companyId): void {
            $this->userRepository->detachFromCompany($user->id, $companyId);
        });

        $this->permissionResolver->invalidate($user->id, $companyId);
    }
}
