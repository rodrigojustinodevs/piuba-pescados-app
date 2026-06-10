<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum HarvestType: string
{
    case TOTAL     = 'total';
    case PARTIAL   = 'partial';
    case SELECTIVE = 'selective';
    case EMERGENCY = 'emergency';

    public function label(): string
    {
        return match ($this) {
            self::TOTAL     => 'Total',
            self::PARTIAL   => 'Parcial',
            self::SELECTIVE => 'Seletiva',
            self::EMERGENCY => 'Emergencial',
        };
    }
}
