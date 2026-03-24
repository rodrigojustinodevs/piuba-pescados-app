<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Enums\StockTransactionReferenceType;
use App\Domain\Enums\Unit;

final readonly class StockTransactionDTO
{
    public function __construct(
        public string $companyId,
        public float $quantity,
        public float $unitPrice,
        public float $totalCost,
        public Unit $unit,
        public StockTransactionDirection $direction,
        public ?string $supplyId = null,
        public ?string $supplierId = null,
        public ?string $referenceId = null,
        public ?StockTransactionReferenceType $referenceType = null,
    ) {
    }
}
