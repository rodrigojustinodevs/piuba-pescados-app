<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum PriceGroup: string
{
    case WHOLESALE = 'wholesale';
    case RETAIL    = 'retail';
    case CONSUMER  = 'consumer';

    public function label(): string
    {
        return match ($this) {
            self::WHOLESALE => 'Wholesale',
            self::RETAIL    => 'Retail',
            self::CONSUMER  => 'Consumer',
        };
    }
}
