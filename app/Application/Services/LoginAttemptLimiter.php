<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\ValueObjects\Email;
use Illuminate\Cache\RateLimiter;

final class LoginAttemptLimiter
{
    private const MAX_ATTEMPTS  = 5;
    private const DECAY_SECONDS = 60;

    public function __construct(
        private readonly RateLimiter $limiter,
    ) {}

    public function tooManyAttempts(Email $email): bool
    {
        return $this->limiter->tooManyAttempts(
            key:     $this->key($email),
            maxAttempts: self::MAX_ATTEMPTS,
        );
    }

    public function increment(Email $email): void
    {
        $this->limiter->hit(
            key:     $this->key($email),
            decaySeconds: self::DECAY_SECONDS,
        );
    }

    public function clear(Email $email): void
    {
        $this->limiter->clear($this->key($email));
    }

    public function ensureNotLocked(Email $email): void
    {
        if ($this->tooManyAttempts($email)) {
            $seconds = $this->limiter->availableIn($this->key($email));

            throw new UnauthorizedException(
                "Too many login attempts. Try again in {$seconds} seconds."
            );
        }
    }

    private function key(Email $email): string
    {
        return 'login:' . $email->value();
    }
}