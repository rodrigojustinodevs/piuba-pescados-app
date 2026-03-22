<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum StockTransactionDirection: string
{
    case IN  = 'in';
    case OUT = 'out';

    public function isIn(): bool
    {
        return $this === self::IN;
    }

    public function label(): string
    {
        return match ($this) {
            self::IN  => 'In',
            self::OUT => 'Out',
        };
    }
}