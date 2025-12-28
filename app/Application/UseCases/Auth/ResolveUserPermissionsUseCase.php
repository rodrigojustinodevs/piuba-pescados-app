<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Domain\Models\User;
use App\Domain\Repositories\AuthRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ResolveUserPermissionsUseCase
{
    private const CACHE_TTL = 600;

    public function __construct(
        protected AuthRepositoryInterface $authRepository
    ) {
    }

    /**
     * @return Collection<int, string>
     */
    public function execute(User $user, ?string $companyId = null): Collection
    {
        $cacheKey = $this->getCacheKey($user->id, $companyId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user, $companyId) {
            return $this->loadPermissionsFromRepository($user, $companyId);
        });
    }

    public function invalidateCache(string $userId, ?string $companyId = null): void
    {
        $cacheKey = $this->getCacheKey($userId, $companyId);
        Cache::forget($cacheKey);
    }

    public function invalidateAllUserCache(string $userId): void
    {
        Cache::forget($this->getCacheKey($userId, null));
    }

    /**
     * @return Collection<int, string>
     */
    private function loadPermissionsFromRepository(User $user, ?string $companyId): Collection
    {
        if ($this->authRepository->isMasterAdmin($user)) {
            return $this->authRepository->getAllPermissions();
        }

        $allPermissions = collect();

        $directPermissionsGlobal = $this->authRepository->getUserDirectPermissions($user);
        $allPermissions = $allPermissions->merge($directPermissionsGlobal);

        if ($companyId) {
            $directPermissionsCompany = $this->authRepository->getUserDirectPermissionsByCompany($user, $companyId);
            $allPermissions = $allPermissions->merge($directPermissionsCompany);
        }

        $rolePermissionsGlobal = $this->authRepository->getUserRolePermissions($user);
        $allPermissions = $allPermissions->merge($rolePermissionsGlobal);

        if ($companyId) {
            $rolePermissionsCompany = $this->authRepository->getUserRolePermissionsByCompany($user, $companyId);
            $allPermissions = $allPermissions->merge($rolePermissionsCompany);
        }

        return $allPermissions->unique()->values();
    }

    private function getCacheKey(string $userId, ?string $companyId): string
    {
        return "user:{$userId}:permissions" . ($companyId ? ":company:{$companyId}" : '');
    }
}
