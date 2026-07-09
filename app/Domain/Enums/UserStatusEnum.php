<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum UserStatusEnum: string
{
    case ACTIVE   = 'active';
    case INACTIVE = 'inactive';
    case BLOCKED  = 'blocked';
    case PENDING  = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE   => 'Ativo',
            self::INACTIVE => 'Inativo',
            self::BLOCKED  => 'Bloqueado',
            self::PENDING  => 'Pendente',
        };
    }
}
