<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final class CapacityLiters
{
    public function __construct(
        private readonly int $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->value < 1) {
            throw new InvalidArgumentException('Capacity must be at least 1 liter.');
        }

        if ($this->value > 1_000_000_000) {
            throw new InvalidArgumentException('Capacity cannot exceed 1,000,000,000 liters.');
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return (string) $this->value;
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }
}
