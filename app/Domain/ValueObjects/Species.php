<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Species
{
    public function __construct(
        private string $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (in_array(trim($this->value), ['', '0'], true)) {
            throw new InvalidArgumentException('Species cannot be empty.');
        }

        if (mb_strlen(trim($this->value)) < 2) {
            throw new InvalidArgumentException('Species must have at least 2 characters.');
        }

        if (mb_strlen(trim($this->value)) > 255) {
            throw new InvalidArgumentException('Species must not exceed 255 characters.');
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
