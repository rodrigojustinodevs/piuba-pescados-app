<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\LoginCredentialsDTO;
use App\Domain\Models\User;
use App\Domain\ValueObjects\Email;

interface AuthRepositoryInterface
{
    public function attemptLogin(LoginCredentialsDTO $credentials): ?string;

    public function findByEmail(Email $email): ?User;
}
