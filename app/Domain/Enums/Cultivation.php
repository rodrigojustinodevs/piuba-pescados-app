<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum Cultivation: string
{
    case GROWOUT = 'growout';
    case NURSERY = 'nursery';
}
