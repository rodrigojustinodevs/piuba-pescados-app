<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum ClientStatusEnum: string
{
    case ACTIVE   = 'active';
    case INACTIVE = 'inactive';
    case PROSPECT = 'prospect';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE   => 'Ativo',
            self::INACTIVE => 'Inativo',
            self::PROSPECT => 'Prospecto',
        };
    }
}
