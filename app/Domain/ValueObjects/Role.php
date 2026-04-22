<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Enums\RolesEnum;

/**
 * Value Object immutable that represents the role of a user in a company.
 */
final readonly class Role implements \Stringable
{
    public RolesEnum $enum;

    public function __construct(string | RolesEnum $role)
    {
        $this->enum = $role instanceof RolesEnum
            ? $role
            : RolesEnum::from($role);
    }

    public static function from(string $value): self
    {
        return new self(RolesEnum::from($value));
    }

    public function isGlobal(): bool
    {
        return $this->enum->isGlobal();
    }

    public function isAtLeast(RolesEnum $required): bool
    {
        return $this->enum->isAtLeast($required);
    }

    public function value(): string
    {
        return $this->enum->value;
    }

    public function label(): string
    {
        return $this->enum->label();
    }

    public function equals(self $other): bool
    {
        return $this->enum === $other->enum;
    }

    public function __toString(): string
    {
        return $this->enum->value;
    }
}
