<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Enums\StockTransactionReferenceType;

final readonly class StockTransactionDTO
{
    public function __construct(
        public string $companyId,
        public string $supplyId,
        public float $quantity,
        public float $unitPrice,
        public float $totalCost,
        public string $unit,
        public StockTransactionDirection $direction,
        public ?string $referenceId = null,
        public ?StockTransactionReferenceType $referenceType = null,
    ) {
    }
}
