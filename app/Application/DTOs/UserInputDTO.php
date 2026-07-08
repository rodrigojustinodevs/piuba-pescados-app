<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class UserInputDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?string $phone = null,
        public ?string $status = null,
        public ?string $position = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name:     isset($data['name']) ? (string) $data['name'] : null,
            email:    isset($data['email']) ? (string) $data['email'] : null,
            password: isset($data['password']) ? (string) $data['password'] : null,
            phone:    isset($data['phone']) ? (string) $data['phone'] : null,
            status:   isset($data['status']) ? (string) $data['status'] : null,
            position: isset($data['position']) ? (string) $data['position'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toPersistence(): array
    {
        return array_filter([
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => $this->password,
            'phone'    => $this->phone,
            'status'   => $this->status,
            'position' => $this->position,
        ], static fn (?string $v): bool => $v !== null);
    }
}
