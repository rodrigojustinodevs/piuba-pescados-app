<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;
use App\Infrastructure\Security\PermissionResolver;
use Illuminate\Support\Facades\DB;

final readonly class UpdateUserStatusUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PermissionResolver $permissionResolver,
    ) {
    }

    /**
     * @throws \DomainException se o usuário não pertencer à empresa resolvida.
     */
    public function execute(string $userId, bool $isActive, ?string $requestedCompanyId): void
    {
        $companyId = CompanyContext::resolveTargetCompanyId($requestedCompanyId);
        $user      = $this->userRepository->findOrFail($userId);

        if (! $user->companies()->where('companies.id', $companyId)->exists()) {
            throw new \DomainException('Usuário não pertence a esta empresa.');
        }

        DB::transaction(function () use ($user, $companyId, $isActive): void {
            $this->userRepository->updateCompanyStatus($user->id, $companyId, $isActive);
        });

        $this->permissionResolver->invalidate($user->id, $companyId);
    }
}
