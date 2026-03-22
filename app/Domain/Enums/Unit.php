<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum Unit: string
{
    case KG    = 'kg';
    case G     = 'g';
    case LITER = 'liter';
    case ML    = 'ml';
    case UNIT  = 'unit';
    case BOX   = 'box';
    case PIECE = 'piece';
    case TON   = 'ton';
}
