<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum Status: string
{
    case ACTIVE   = 'active';
    case INACTIVE = 'inactive';
}
