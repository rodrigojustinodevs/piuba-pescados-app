<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\LoginCredentialsDTO;
use App\Domain\Repositories\AuthRetositoryInterface;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthRepository implements AuthRetositoryInterface
{
    public function attemptLogin(LoginCredentialsDTO $credentials): ?string
    {
        try {
            $token = JWTAuth::attempt([
                'email'    => $credentials->email,
                'password' => $credentials->password,
            ]);

            return $token ?: null;
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
