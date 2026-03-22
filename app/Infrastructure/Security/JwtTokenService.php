<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Contracts\Auth\TokenServiceInterface;
use App\Domain\Models\User;
use RuntimeException;
use Tymon\JWTAuth\Facades\JWTAuth;

final class JwtTokenService implements TokenServiceInterface
{
    public function issue(User $user): string
    {
        $token = JWTAuth::fromUser($user);

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Unable to issue token.');
        }

        return $token;
    }

    public function invalidate(): void
    {
        JWTAuth::parseToken()->invalidate();
    }

    public function refresh(): string
    {
        $token = JWTAuth::parseToken()->refresh();

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Unable to refresh token.');
        }

        return $token;
    }

    public function ttlInSeconds(): int
    {
        return (int) config('jwt.ttl', 60) * 60;
    }
}