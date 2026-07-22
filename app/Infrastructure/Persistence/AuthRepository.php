<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\LoginCredentialsDTO;
use App\Domain\Models\User;
use App\Domain\Repositories\AuthRepositoryInterface;
use App\Domain\ValueObjects\Email;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthRepository implements AuthRepositoryInterface
{
    public function attemptLogin(LoginCredentialsDTO $credentials): ?string
    {
        try {
            /** @var StatefulGuard $guard */
            $guard = Auth::guard('api');

            /** @var string|false $token */
            $token = $guard->attempt([
                'email'    => $credentials->email,
                'password' => $credentials->password,
            ]);

            return $token !== false ? $token : null;
        } catch (JWTException) {
            return null;
        }
    }

    public function findByEmail(Email $email): ?User
    {
        return User::with('roles', 'permissions', 'companies')->where('email', $email->value())->first();
    }
}
