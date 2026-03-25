<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum StockingHistoryEvent: string
{
    case BIOMETRY   = 'biometry';
    case MORTALITY  = 'mortality';
    case TRANSFER   = 'transfer';
    case MEDICATION = 'medication';

    public function label(): string
    {
        return match ($this) {
            self::BIOMETRY   => 'Biometria',
            self::MORTALITY  => 'Mortalidade',
            self::TRANSFER   => 'Transferência',
            self::MEDICATION => 'Medicação',
        };
    }

    /** Whether this event requires a quantity (number of fish). */
    public function requiresQuantity(): bool
    {
        return match ($this) {
            self::MORTALITY, self::TRANSFER => true,
            default => false,
        };
    }

    /** Whether this event requires an average weight measurement. */
    public function requiresAverageWeight(): bool
    {
        return $this === self::BIOMETRY;
    }
}
