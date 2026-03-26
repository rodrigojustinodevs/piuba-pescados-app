<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum BatchStatus: string
{
    case ACTIVE   = 'active';
    case FINISHED = 'finished';

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isFinished(): bool
    {
        return $this === self::FINISHED;
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE   => 'Active',
            self::FINISHED => 'Finished',
        };
    }
}
