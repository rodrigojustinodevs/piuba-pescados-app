<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum Cultivation: string
{
    case DAYCARE = 'daycare';
    case NURSERY = 'nursery';
}
