<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum SaleStatus: string
{
    case PENDING   = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Pendente',
            self::CONFIRMED => 'Confirmada',
            self::CANCELLED => 'Cancelada',
        };
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function isConfirmed(): bool
    {
        return $this === self::CONFIRMED;
    }
}
