<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Enums\StockTransactionReferenceType;
use App\Domain\Enums\Unit;

/**
 * @property string $companyId
 * @property float $quantity
 * @property float $unitPrice
 * @property float $totalCost
 * @property Unit $unit
 * @property StockTransactionDirection $direction
 * @property string|null $supplyId
 * @property string|null $referenceId
 * @property StockTransactionReferenceType|null $referenceType
 */
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
        public ?string $referenceId = null,
        public ?StockTransactionReferenceType $referenceType = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(): array
    {
        return array_filter([
            'company_id'     => $this->companyId,
            'quantity'       => $this->quantity,
            'unit_price'     => $this->unitPrice,
            'total_cost'     => $this->totalCost,
            'unit'           => $this->unit->value,
            'direction'      => $this->direction->value,
            'supply_id'      => $this->supplyId,
            'reference_id'   => $this->referenceId,
            'reference_type' => $this->referenceType?->value,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
