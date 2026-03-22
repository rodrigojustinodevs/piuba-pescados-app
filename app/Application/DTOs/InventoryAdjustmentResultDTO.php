<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Models\InventoryAdjustment;
use App\Domain\Models\Stock;

final readonly class InventoryAdjustmentResultDTO
{
    public function __construct(
        public InventoryAdjustment $adjustment,
        public Stock $stock,
        public float $delta,
    ) {
    }

    public function isLoss(): bool
    {
        return $this->delta < 0;
    }

    public function isGain(): bool
    {
        return $this->delta > 0;
    }
}
