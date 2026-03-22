<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Quantity
{
    private float $value;

    private function __construct(float $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero');
        }

        $this->value = $value;
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function value(): float
    {
        return $this->value;
    }

    public function add(self $quantity): self
    {
        return new self($this->value + $quantity->value);
    }

    public function subtract(self $quantity): self
    {
        $result = $this->value - $quantity->value;

        if ($result <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero');
        }

        return new self($result);
    }

    public function multiply(float $factor): self
    {
        return new self($this->value * $factor);
    }

    public function equals(self $quantity): bool
    {
        return $this->value === $quantity->value;
    }
}
