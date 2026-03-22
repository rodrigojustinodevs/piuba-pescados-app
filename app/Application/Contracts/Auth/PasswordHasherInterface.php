<?php

declare(strict_types=1);

namespace App\Application\Contracts\Auth;

use App\Domain\ValueObjects\PlainPassword;

interface PasswordHasherInterface
{
    public function check(PlainPassword $plain, string $hashed): bool;
}