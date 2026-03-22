<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final class Money
{
    private int $amount;

    private function __construct(int $amount)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money cannot be negative');
        }

        $this->amount = $amount;
    }

    public static function fromFloat(float $value): self
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Money cannot be negative');
        }

        return new self((int) round($value * 100));
    }

    public static function fromInt(int $cents): self
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Money cannot be negative');
        }

        return new self($cents);
    }

    public function toFloat(): float
    {
        return $this->amount / 100;
    }

    /**
     * Atalho compatível com o enunciado.
     */
    public function value(): float
    {
        return $this->toFloat();
    }

    public function toInt(): int
    {
        return $this->amount;
    }

    public function add(self $money): self
    {
        return new self($this->amount + $money->amount);
    }

    public function subtract(self $money): self
    {
        $result = $this->amount - $money->amount;

        if ($result < 0) {
            throw new InvalidArgumentException('Money cannot be negative');
        }

        return new self($result);
    }

    public function multiply(float $factor): self
    {
        return new self((int) round($this->amount * $factor));
    }

    public function equals(self $money): bool
    {
        return $this->amount === $money->amount;
    }
}
