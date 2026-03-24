<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum FinancialCategoryStatus: string
{
    case ACTIVE   = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE   => 'active',
            self::INACTIVE => 'inactive',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}
