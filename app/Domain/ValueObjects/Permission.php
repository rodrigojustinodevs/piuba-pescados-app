<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Permission
{
    public function __construct(
        private string $name
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->name === '' || $this->name === '0') {
            throw new InvalidArgumentException('Permission name cannot be empty.');
        }

        if (in_array(preg_match('/^[a-z]+(-[a-z]+)+$/', $this->name), [0, false], true)) {
            throw new InvalidArgumentException("Invalid permission format: {$this->name}");
        }
    }

    public function value(): string
    {
        return $this->name;
    }

    public function equals(self $other): bool
    {
        return $this->name === $other->name;
    }

    public function toString(): string
    {
        return $this->name;
    }

    public static function fromString(string $name): self
    {
        return new self($name);
    }
}
