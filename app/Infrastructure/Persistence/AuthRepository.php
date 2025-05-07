<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\LoginCredentialsDTO;
use App\Domain\Repositories\AuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthRepository implements AuthRepositoryInterface
{
    public function attemptLogin(LoginCredentialsDTO $credentials): ?string
    {
        try {
            /** @var string|false $token */
            $token = auth('api')->attempt([
                'email'    => $credentials->email,
                'password' => $credentials->password,
            ]);

            return $token !== false ? $token : null;
        } catch (JWTException) {
            return null;
        }
    }

    public function userHasRole(string $role): bool
    {
        return Auth::user()
            ->roles()
            ->where('name', $role)
            ->exists();
    }

    public function userHasPermission(string $permission): bool
    {
        if (
            Auth::user()
                ->permissions()
                ->where('name', $permission)
                ->exists()
        ) {
            return true;
        }

        return (bool) Auth::user()
            ->roles()
            ->whereHas('permissions', fn ($q) => $q->where('name', $permission))
            ->exists();
    }
}
