<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final class PlainPassword
{
    private readonly string $value;

    public function __construct(string $value)
    {
        if (strlen($value) < 6) {
            throw new InvalidArgumentException('Password must be at least 6 characters.');
        }

        $this->value = $value;
    }

    public static function of(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * Impede que a senha vaze em logs, var_dump, json_encode, etc.
     */
    public function __toString(): string
    {
        return '***';
    }

    public function __debugInfo(): array
    {
        return ['value' => '***'];
    }
}