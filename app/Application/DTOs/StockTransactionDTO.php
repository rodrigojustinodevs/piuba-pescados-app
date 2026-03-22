<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Enums\StockTransactionReferenceType;

final class StockTransactionDTO
{
    public function __construct(
        public readonly string                        $companyId,
        public readonly string                        $supplyId,
        public readonly float                         $quantity,
        public readonly float                         $unitPrice,
        public readonly float                         $totalCost,
        public readonly string                        $unit,
        public readonly StockTransactionDirection     $direction,
        public readonly ?string                       $referenceId    = null,
        public readonly ?StockTransactionReferenceType $referenceType = null,
    ) {}
}