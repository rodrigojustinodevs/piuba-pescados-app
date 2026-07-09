<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum SaleStatus: string
{
    case PENDING   = 'pending';
    case CONFIRMED = 'confirmed';
    case PAID      = 'paid';
    case DELIVERED = 'delivered';
    case OVERDUE   = 'overdue';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Pendente',
            self::CONFIRMED => 'Confirmada',
            self::PAID      => 'Paga',
            self::DELIVERED => 'Entregue',
            self::OVERDUE   => 'Atrasada',
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

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function isDelivered(): bool
    {
        return $this === self::DELIVERED;
    }

    public function isFinanciallySettled(): bool
    {
        return in_array($this, [self::PAID, self::DELIVERED], true);
    }

    /** Returns statuses that cannot be cancelled. */
    public function isCancellable(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED, self::OVERDUE], true);
    }
}
