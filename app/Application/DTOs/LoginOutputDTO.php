<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Application\DTOs\UserContextDTO;

final class LoginOutputDTO
{
    public function __construct(
        public readonly string $token,
        public readonly string $tokenType,
        public readonly int    $expiresIn,
        public readonly UserContextDTO $user,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'token'     => $this->token,
            'tokenType' => $this->tokenType,
            'expiresIn' => $this->expiresIn,
            'user'      => $this->user->toArray(),
        ];
    }
}