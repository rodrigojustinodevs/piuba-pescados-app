<?php

namespace App\Domain\Repositories;

use App\Application\DTOs\LoginCredentialsDTO;

interface AuthRepositoryInterface
{
    public function attemptLogin(LoginCredentialsDTO $credentials): ?string;
    public function userHasRole(string $role): bool;
    public function userHasPermission(string $permission): bool;
}
