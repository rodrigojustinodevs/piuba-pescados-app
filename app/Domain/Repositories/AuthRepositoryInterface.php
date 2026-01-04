<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\LoginCredentialsDTO;
use App\Domain\Models\User;
use Illuminate\Support\Collection;

interface AuthRepositoryInterface
{
    public function attemptLogin(LoginCredentialsDTO $credentials): ?string;

    public function userHasRole(string $role): bool;

    public function userHasPermission(string $permission): bool;

    public function isMasterAdmin(User $user): bool;

    /**
     * @return Collection<int, string>
     */
    public function getAllPermissions(): Collection;

    /**
     * @return Collection<int, string>
     */
    public function getUserDirectPermissions(User $user): Collection;

    /**
     * @return Collection<int, string>
     */
    public function getUserDirectPermissionsByCompany(User $user, string $companyId): Collection;

    /**
     * @return Collection<int, string>
     */
    public function getUserRolePermissions(User $user): Collection;

    /**
     * @return Collection<int, string>
     */
    public function getUserRolePermissionsByCompany(User $user, string $companyId): Collection;
}
