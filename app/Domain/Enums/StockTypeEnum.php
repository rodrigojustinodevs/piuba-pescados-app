<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum StockTypeEnum: string
{
    case WAREHOUSE = 'warehouse';
    case COLD_ROOM = 'cold_room';
    case SILO      = 'silo';
    case STORAGE   = 'storage';
    case FIELD     = 'field';

    public function label(): string
    {
        return match ($this) {
            self::WAREHOUSE => 'Galpão',
            self::COLD_ROOM => 'Câmara Fria',
            self::SILO      => 'Silo',
            self::STORAGE   => 'Armazém',
            self::FIELD     => 'Campo',
        };
    }
}
