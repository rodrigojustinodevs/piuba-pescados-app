<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Phone
{
    public function __construct(
        private string $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $phone = $this->sanitize();

        if ($phone === '' || $phone === '0') {
            throw new InvalidArgumentException('Phone cannot be empty.');
        }

        if (strlen($phone) < 10 || strlen($phone) > 11) {
            throw new InvalidArgumentException('Phone must have 10 or 11 digits.');
        }

        if (in_array(preg_match('/^\d{10,11}$/', $phone), [0, false], true)) {
            throw new InvalidArgumentException("Invalid phone format: {$this->value}");
        }
    }

    private function sanitize(): string
    {
        return preg_replace('/[^0-9]/', '', $this->value);
    }

    public function value(): string
    {
        return $this->sanitize();
    }

    public function formatted(): string
    {
        $phone  = $this->value();
        $length = strlen($phone);

        if ($length === 11) {
            return sprintf(
                '(%s) %s-%s',
                substr($phone, 0, 2),
                substr($phone, 2, 5),
                substr($phone, 7, 4)
            );
        }

        return sprintf(
            '(%s) %s-%s',
            substr($phone, 0, 2),
            substr($phone, 2, 4),
            substr($phone, 6, 4)
        );
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
