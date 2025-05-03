<?php

declare(strict_types=1);

namespace App\Application\UseCases\Auth;

use App\Application\DTOs\LoginCredentialsDTO;
use App\Domain\Repositories\AuthRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AuthenticateUserUseCase
{
    public function __construct(
        protected AuthRepositoryInterface $authManager
    ) {
    }

    /**
     * @return string|null  The JWT token if authentication succeeds, or null otherwise.
     */
    public function execute(LoginCredentialsDTO $credentials): ?string
    {
        return DB::transaction(fn(): ?string => $this->authManager->attemptLogin($credentials));
    }
}
