<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum StockingStatus: string
{
    case ACTIVE = 'active';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Ativo',
            self::CLOSED => 'Encerrado',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isClosed(): bool
    {
        return $this === self::CLOSED;
    }
}
