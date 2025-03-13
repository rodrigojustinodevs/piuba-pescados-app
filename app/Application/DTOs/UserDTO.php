<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class UserDTO
{
    public function __construct(
        public string $id,
        public bool $isAdmin,
        public string $name,
        public string $email,
        public string $password,
        public ?string $emailVerifiedAt = null,
        public ?string $rememberToken = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            isAdmin: (bool) $data['is_admin'],
            name: $data['name'],
            email: $data['email'],
            emailVerifiedAt: $data['email_verified_at'] ?? null,
            password: $data['password'],
            rememberToken: $data['remember_token'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'isAdmin'         => $this->isAdmin,
            'name'            => $this->name,
            'email'           => $this->email,
            'emailVerifiedAt' => $this->emailVerifiedAt,
            'password'        => $this->password,
            'rememberToken'   => $this->rememberToken,
            'createdAt'       => $this->createdAt,
            'updatedAt'       => $this->updatedAt,
        ];
    }
}
