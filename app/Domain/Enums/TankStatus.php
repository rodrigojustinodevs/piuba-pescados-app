<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum TankStatus: string
{
    case ACTIVE      = 'active';
    case INACTIVE    = 'inactive';
    case MAINTENANCE = 'maintenance';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this === self::INACTIVE;
    }

    public function isMaintenance(): bool
    {
        return $this === self::MAINTENANCE;
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE      => 'Active',
            self::INACTIVE    => 'Inactive',
            self::MAINTENANCE => 'Maintenance',
        };
    }
}
