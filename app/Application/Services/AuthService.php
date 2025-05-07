<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\LoginCredentialsDTO;
use App\Application\UseCases\Auth\AuthenticateUserUseCase;

class AuthService
{
    public function __construct(
        protected AuthenticateUserUseCase $authenticateUserUseCase
    ) {
    }

    /**
     * Returns the token string if successful, or null if authentication fails.
     */
    public function authenticate(LoginCredentialsDTO $credentials): ?string
    {
        return $this->authenticateUserUseCase->execute($credentials);
    }
}
