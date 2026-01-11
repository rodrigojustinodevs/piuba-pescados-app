<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class EntryDate
{
    public function __construct(
        private CarbonImmutable $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $now     = CarbonImmutable::now();
        $minDate = CarbonImmutable::create(1900, 1, 1);

        if ($this->value->isBefore($minDate)) {
            throw new InvalidArgumentException('Entry date cannot be before 1900-01-01.');
        }

        if ($this->value->isAfter($now->addYear())) {
            throw new InvalidArgumentException('Entry date cannot be more than 1 year in the future.');
        }
    }

    public function value(): CarbonImmutable
    {
        return $this->value;
    }

    public function toDateString(): string
    {
        return $this->value->toDateString();
    }

    public function toDateTimeString(): string
    {
        return $this->value->toDateTimeString();
    }

    public function equals(self $other): bool
    {
        return $this->value->equalTo($other->value);
    }

    public function toString(): string
    {
        return $this->value->toDateString();
    }

    public static function fromString(string $value): self
    {
        try {
            $date = CarbonImmutable::parse($value);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Invalid date format: {$value}", 0, $e);
        }

        return new self($date);
    }

    public static function fromCarbon(CarbonImmutable $value): self
    {
        return new self($value);
    }
}
