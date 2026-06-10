<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum HarvestStatus: string
{
    case COMPLETED   = 'completed';
    case SCHEDULED   = 'scheduled';
    case IN_PROGRESS = 'in_progress';
    case CANCELLED   = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::COMPLETED   => 'Concluída',
            self::SCHEDULED   => 'Agendada',
            self::IN_PROGRESS => 'Em andamento',
            self::CANCELLED   => 'Cancelada',
        };
    }
}
