<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum StockMovementTypeEnum: string
{
    case ENTRY      = 'entry';
    case EXIT       = 'exit';
    case ADJUSTMENT = 'adjustment';
    case TRANSFER   = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::ENTRY      => 'Entrada',
            self::EXIT       => 'Saída',
            self::ADJUSTMENT => 'Ajuste',
            self::TRANSFER   => 'Transferência',
        };
    }
}
