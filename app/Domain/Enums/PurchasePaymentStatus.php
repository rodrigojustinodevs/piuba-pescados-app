<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum PurchasePaymentStatus: string
{
    case PENDING = 'pending';
    case PARTIAL = 'partial';
    case PAID    = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::PARTIAL => 'Parcial',
            self::PAID    => 'Pago',
        };
    }
}
