<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum SensorType: string
{
    case PH          = 'ph';
    case TEMPERATURE = 'temperature';
    case OXYGEN      = 'oxygen';
    case AMMONIA     = 'ammonia';
}
