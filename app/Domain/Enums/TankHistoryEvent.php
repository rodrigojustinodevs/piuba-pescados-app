<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum TankHistoryEvent: string
{
    case CLEANING    = 'cleaning';
    case MAINTENANCE = 'maintenance';
    case FALLOWING   = 'fallowing';

    public function label(): string
    {
        return match ($this) {
            self::CLEANING    => 'Limpeza',
            self::MAINTENANCE => 'Manutenção',
            self::FALLOWING   => 'Pousio',
        };
    }

    /** Events that place the tank in an unavailable state for new stockings. */
    public function blocksNewAllocations(): bool
    {
        return match ($this) {
            self::CLEANING, self::MAINTENANCE, self::FALLOWING => true,
        };
    }
}
