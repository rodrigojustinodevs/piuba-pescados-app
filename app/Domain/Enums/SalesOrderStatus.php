<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum SalesOrderStatus: string
{
    case DRAFT     = 'draft';
    case OPEN      = 'open';
    case SENT      = 'sent';
    case EXPIRED   = 'expired';
    case PAID      = 'paid';
    case APPROVED  = 'approved';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case FINISHED  = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT     => 'Draft',
            self::OPEN      => 'Open',
            self::SENT      => 'Sent',
            self::EXPIRED   => 'Expired',
            self::APPROVED  => 'Approved',
            self::CONFIRMED => 'Confirmed',
            self::PAID      => 'Paid',
            self::CANCELLED => 'Cancelled',
            self::FINISHED  => 'Finished',
        };
    }

    /**
     * Orçamento ainda pode ter cabeçalho e itens alterados.
     */
    public function allowsQuotationEditing(): bool
    {
        return match ($this) {
            self::DRAFT, self::OPEN => true,
            default => false,
        };
    }
}
