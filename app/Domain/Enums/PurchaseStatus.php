<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum PurchaseStatus: string
{
    case DRAFT     = 'draft';
    case CONFIRMED = 'confirmed';
    case RECEIVED  = 'received';
    case CANCELLED = 'cancelled';

    public function isReceived(): bool
    {
        return $this === self::RECEIVED;
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::DRAFT     => in_array($next, [self::CONFIRMED, self::CANCELLED], strict: true),
            self::CONFIRMED => in_array($next, [self::RECEIVED,  self::CANCELLED], strict: true),
            self::RECEIVED,
            self::CANCELLED => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT     => 'Draft',
            self::CONFIRMED => 'Confirmed',
            self::RECEIVED  => 'Received',
            self::CANCELLED => 'Cancelled',
        };
    }
}