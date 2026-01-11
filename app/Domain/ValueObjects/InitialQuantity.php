<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class InitialQuantity
{
    public function __construct(
        private int $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->value < 1) {
            throw new InvalidArgumentException('Initial quantity must be at least 1.');
        }

        if ($this->value > 10_000_000) {
            throw new InvalidArgumentException('Initial quantity cannot exceed 10,000,000.');
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
