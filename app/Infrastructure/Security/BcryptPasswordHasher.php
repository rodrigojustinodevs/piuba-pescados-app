<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Contracts\Auth\PasswordHasherInterface;
use App\Domain\ValueObjects\PlainPassword;
use Illuminate\Support\Facades\Hash;

final class BcryptPasswordHasher implements PasswordHasherInterface
{
    public function check(PlainPassword $plain, string $hashed): bool
    {
        return Hash::check($plain->value(), $hashed);
    }
}