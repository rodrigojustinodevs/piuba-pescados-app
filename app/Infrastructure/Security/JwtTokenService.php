<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Contracts\Auth\TokenServiceInterface;
use App\Domain\Models\User;
use RuntimeException;
use Tymon\JWTAuth\JWTAuth;

final class JwtTokenService implements TokenServiceInterface
{
    public function __construct(
        private readonly JWTAuth $jwt,
    ) {
    }

    public function issue(User $user): string
    {
        $token = $this->jwt->fromUser($user);

        if ($token === '') {
            throw new RuntimeException('Unable to issue token.');
        }

        return $token;
    }

    public function invalidate(): void
    {
        $this->jwt->parseToken()->invalidate();
    }

    public function refresh(): string
    {
        $token = $this->jwt->parseToken()->refresh();

        if ($token === '') {
            throw new RuntimeException('Unable to refresh token.');
        }

        return $token;
    }

    public function ttlInSeconds(): int
    {
        return (int) config('jwt.ttl', 60) * 60;
    }
}
