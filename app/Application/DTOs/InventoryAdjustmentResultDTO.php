<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Models\InventoryAdjustment;
use App\Domain\Models\Stock;

final class InventoryAdjustmentResultDTO
{
    public function __construct(
        public readonly InventoryAdjustment $adjustment,
        public readonly Stock               $stock,
        public readonly float               $delta,
    ) {}

    public function isLoss(): bool
    {
        return $this->delta < 0;
    }

    public function isGain(): bool
    {
        return $this->delta > 0;
    }
}