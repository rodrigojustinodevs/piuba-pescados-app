<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class LoginCredentialsDTO
{
    public function __construct(
        public string $email,
        public string $password
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password']
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'email'    => $this->email,
            'password' => $this->password,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->email === '' || $this->email === '0') &&
            ($this->password === '' || $this->password === '0');
    }
}
