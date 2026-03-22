<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum InventoryAdjustmentStatus: string
{
    case PENDING   = 'pending';
    case APPLIED   = 'applied';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Pendente',
            self::APPLIED   => 'Aplicado',
            self::CANCELLED => 'Cancelado',
        };
    }
}
