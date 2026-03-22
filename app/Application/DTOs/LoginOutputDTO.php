<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class LoginOutputDTO
{
    public function __construct(
        public string $token,
        public string $tokenType,
        public int $expiresIn,
        public UserContextDTO $user,
    ) {
    }

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
