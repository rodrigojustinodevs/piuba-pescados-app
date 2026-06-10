<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum HarvestDestination: string
{
    case WHOLESALE  = 'wholesale';
    case RETAIL     = 'retail';
    case PROCESSING = 'processing';
    case RESTAURANT = 'restaurant';
    case LIVE_MARKET = 'live_market';
    case INTERNAL   = 'internal';

    public function label(): string
    {
        return match ($this) {
            self::WHOLESALE   => 'Atacado',
            self::RETAIL      => 'Varejo',
            self::PROCESSING  => 'Beneficiamento',
            self::RESTAURANT  => 'Restaurante',
            self::LIVE_MARKET => 'Peixe Vivo',
            self::INTERNAL    => 'Consumo Interno',
        };
    }
}
