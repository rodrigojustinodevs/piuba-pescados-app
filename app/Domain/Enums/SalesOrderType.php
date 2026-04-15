<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum SalesOrderType: string
{
    case QUOTATION = 'quotation';
    case ORDER     = 'order';

    public function label(): string
    {
        return match ($this) {
            self::QUOTATION => 'Quotation',
            self::ORDER     => 'Order',
        };
    }
}
