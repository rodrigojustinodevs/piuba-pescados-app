<?php

declare(strict_types=1);

namespace App\Application\Contracts\Auth;

use App\Domain\Models\User;

interface TokenServiceInterface
{
    public function issue(User $user): string;

    public function invalidate(): void;

    public function refresh(): string;

    public function ttlInSeconds(): int;
}
