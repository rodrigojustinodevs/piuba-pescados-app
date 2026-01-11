<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class UserId
{
    public function __construct(
        private string $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->value === '' || $this->value === '0') {
            throw new InvalidArgumentException('User ID cannot be empty.');
        }

        if (! Str::isUuid($this->value)) {
            throw new InvalidArgumentException("Invalid UUID format: {$this->value}");
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function generate(): self
    {
        return new self((string) Str::uuid());
    }
}
