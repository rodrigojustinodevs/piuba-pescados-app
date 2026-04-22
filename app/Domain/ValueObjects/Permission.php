<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Enums\PermissionsEnum;

final readonly class Permission implements \Stringable
{
    public PermissionsEnum $enum;

    public function __construct(string | PermissionsEnum $permission)
    {
        $this->enum = $permission instanceof PermissionsEnum
            ? $permission
            : PermissionsEnum::from($permission);
    }

    public static function from(string $value): self
    {
        return new self(PermissionsEnum::from($value));
    }

    public function value(): string
    {
        return $this->enum->value;
    }

    public function label(): string
    {
        return $this->enum->label();
    }

    public function category(): string
    {
        return $this->enum->category();
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
