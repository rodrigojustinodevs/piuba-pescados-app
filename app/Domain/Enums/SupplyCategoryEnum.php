<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum SupplyCategoryEnum: string
{
    case FEED             = 'feed';
    case MEDICATION       = 'medication';
    case FERTILIZER       = 'fertilizer';
    case PROBIOTIC        = 'probiotic';
    case EQUIPMENT        = 'equipment';
    case PACKAGING        = 'packaging';
    case FINISHED_PRODUCT = 'finished_product';
    case OTHER            = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FEED             => 'Ração',
            self::MEDICATION       => 'Medicamento',
            self::FERTILIZER       => 'Fertilizante',
            self::PROBIOTIC        => 'Probiótico',
            self::EQUIPMENT        => 'Equipamento',
            self::PACKAGING        => 'Embalagem',
            self::FINISHED_PRODUCT => 'Produto Acabado',
            self::OTHER            => 'Outro',
        };
    }
}
