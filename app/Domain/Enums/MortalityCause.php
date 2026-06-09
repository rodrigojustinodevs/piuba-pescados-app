<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum MortalityCause: string
{
    case DISEASE       = 'disease';
    case WATER_QUALITY = 'water_quality';
    case PREDATION     = 'predation';
    case HANDLING      = 'handling';
    case CLIMATE       = 'climate';
    case UNKNOWN       = 'unknown';
    case OTHER         = 'other';
}
