<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Enums\Unit as UnitEnum;
use InvalidArgumentException;

final class Unit
{
    private function __construct(
        private readonly UnitEnum $value
    ) {}

    public static function from(string $value): self
    {
        $enum = UnitEnum::tryFrom($value);
        if ($enum === null) {
            throw new InvalidArgumentException("Invalid unit: {$value}");
        }

        return new self($enum);
    }

    public static function fromEnum(UnitEnum $enum): self
    {
        return new self($enum);
    }

    /** @return list<string> */
    public static function allowed(): array
    {
        return array_map(
            static fn (UnitEnum $case) => $case->value,
            UnitEnum::cases()
        );
    }

    public function value(): string
    {
        return $this->value->value;
    }

    public function toEnum(): UnitEnum
    {
        return $this->value;
    }

    public function equals(self $unit): bool
    {
        return $this->value === $unit->value;
    }
}