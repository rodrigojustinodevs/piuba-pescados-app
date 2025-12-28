<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

readonly class Permission
{
    public function __construct(
        public string $name
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (! preg_match('/^[a-z]+(-[a-z]+)+$/', $this->name)) {
            throw new \InvalidArgumentException("Invalid permission format: {$this->name}");
        }
    }

    public function equals(Permission $other): bool
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
