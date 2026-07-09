<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum PurchaseStatus: string
{
    case DRAFT              = 'draft';
    case SUBMITTED          = 'submitted';
    case APPROVED           = 'approved';
    case PARTIALLY_RECEIVED = 'partially_received';
    case RECEIVED           = 'received';
    case CANCELLED          = 'cancelled';

    public function isReceived(): bool
    {
        return $this === self::RECEIVED || $this === self::PARTIALLY_RECEIVED;
    }

    public function canTransitionTo(self $next): bool
    {
        if ($next === self::CANCELLED) {
            return ! in_array($this, [self::RECEIVED, self::CANCELLED], strict: true);
        }

        return match ($this) {
            self::DRAFT              => $next === self::SUBMITTED,
            self::SUBMITTED          => $next === self::APPROVED,
            self::APPROVED           => in_array($next, [self::PARTIALLY_RECEIVED, self::RECEIVED], strict: true),
            self::PARTIALLY_RECEIVED => $next === self::RECEIVED,
            self::RECEIVED,
            self::CANCELLED => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT              => 'Rascunho',
            self::SUBMITTED          => 'Enviado',
            self::APPROVED           => 'Aprovado',
            self::PARTIALLY_RECEIVED => 'Recebido Parcialmente',
            self::RECEIVED           => 'Recebido',
            self::CANCELLED          => 'Cancelado',
        };
    }
}
