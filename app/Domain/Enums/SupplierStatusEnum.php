<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum SupplierStatusEnum: string
{
    case ACTIVE    = 'active';
    case INACTIVE  = 'inactive';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE    => 'Ativo',
            self::INACTIVE  => 'Inativo',
            self::SUSPENDED => 'Suspenso',
        };
    }
}
