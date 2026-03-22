<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\PlainPassword;

final class LoginInputDTO
{
    public function __construct(
        public readonly Email         $email,
        public readonly PlainPassword $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email:    Email::of((string) $data['email']),
            password: PlainPassword::of((string) $data['password']),
        );
    }
}