<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum FinancialTransactionStatus: string
{
    case PENDING   = 'pending';
    case PAID      = 'paid';
    case OVERDUE   = 'overdue';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Pending',
            self::PAID      => 'Paid',
            self::OVERDUE   => 'Overdue',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::PENDING   => in_array($next, [self::PAID, self::OVERDUE, self::CANCELLED], strict: true),
            self::OVERDUE   => in_array($next, [self::PAID, self::CANCELLED], strict: true),
            self::PAID      => $next === self::PENDING,
            self::CANCELLED => false,
        };
    }
}
