<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Models\User;

final readonly class UserContextDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public ?string $companyId,
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            id:        (string) $user->id,
            name:      (string) $user->name,
            email:     (string) $user->email,
            companyId: (function () use ($user): ?string {
                $attrs = $user->getAttributes();

                return isset($attrs['company_id']) ? (string) $attrs['company_id'] : null;
            })(),
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
