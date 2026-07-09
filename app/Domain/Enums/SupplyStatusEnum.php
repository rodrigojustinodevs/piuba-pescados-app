<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum SupplyStatusEnum: string
{
    case ACTIVE    = 'active';
    case INACTIVE  = 'inactive';
    case LOW_STOCK = 'low_stock';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE    => 'Ativo',
            self::INACTIVE  => 'Inativo',
            self::LOW_STOCK => 'Estoque Baixo',
        };
    }
}
