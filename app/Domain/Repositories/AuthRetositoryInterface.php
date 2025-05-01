<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\LoginCredentialsDTO;

interface AuthRetositoryInterface
{
    /**
     * @return string|null JWT token if successful, or null on failure.
     */
    public function attemptLogin(LoginCredentialsDTO $credentials): ?string;

    public function userHasRole(string $role): bool;

    public function userHasPermission(string $permission): bool;
}
