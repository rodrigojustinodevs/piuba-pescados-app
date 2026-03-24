<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum AllocationMethod: string
{
    case FLAT    = 'flat';
    case BIOMASS = 'biomass';
    case VOLUME  = 'volume';

    public function label(): string
    {
        return match ($this) {
            self::FLAT    => 'Igualitário',
            self::BIOMASS => 'Por Biomassa',
            self::VOLUME  => 'Por Volume (m³)',
        };
    }
}
