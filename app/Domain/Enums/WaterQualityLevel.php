<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum WaterQualityLevel: string
{
    case EXCELLENT = 'excellent';
    case GOOD      = 'good';
    case WARNING   = 'warning';
    case CRITICAL  = 'critical';
    case UNKNOWN   = 'unknown';
}
