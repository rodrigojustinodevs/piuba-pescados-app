<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum SupplierCategoryEnum: string
{
    case FEED       = 'feed';
    case MEDICATION = 'medication';
    case EQUIPMENT  = 'equipment';
    case SUPPLY     = 'supply';
    case SERVICE    = 'service';
    case LOGISTICS  = 'logistics';
    case OTHER      = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FEED       => 'Ração',
            self::MEDICATION => 'Medicamento',
            self::EQUIPMENT  => 'Equipamento',
            self::SUPPLY     => 'Insumo',
            self::SERVICE    => 'Serviço',
            self::LOGISTICS  => 'Logística',
            self::OTHER      => 'Outro',
        };
    }
}
