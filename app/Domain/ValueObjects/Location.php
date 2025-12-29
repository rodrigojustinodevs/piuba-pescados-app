<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final class Location
{
    public function __construct(
        private readonly string $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty(trim($this->value))) {
            throw new InvalidArgumentException('Location cannot be empty.');
        }

        if (mb_strlen(trim($this->value)) < 3) {
            throw new InvalidArgumentException('Location must have at least 3 characters.');
        }

        if (mb_strlen(trim($this->value)) > 255) {
            throw new InvalidArgumentException('Location must not exceed 255 characters.');
        }
    }

    public function value(): string
    {
        return trim($this->value);
    }

    public function equals(self $other): bool
    {
        return $this->value() === $other->value();
    }

    public function toString(): string
    {
        return $this->value();
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
