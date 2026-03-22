<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Models\User;

final class UserContextDTO
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $name,
        public readonly string  $email,
        public readonly ?string $companyId,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id:        (string) $user->id,
            name:      (string) $user->name,
            email:     (string) $user->email,
            companyId: isset($user->getAttributes()['company_id'])
                ? (string) $user->company_id
                : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'email'     => $this->email,
            'companyId' => $this->companyId,
        ];
    }
}