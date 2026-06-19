<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\StockStatusEnum;
use App\Domain\Enums\StockTypeEnum;

final readonly class StockInputDTO
{
    public function __construct(
        public string $companyId,
        public ?string $supplyId,
        public float $quantity,
        public string $unit,
        public float $unitPrice,
        public float $totalCost,
        public float $minimumStock,
        public float $withdrawalQuantity,
        public ?string $supplierId = null,
        public ?string $referenceId = null,
        // Location fields
        public ?string $code = null,
        public ?string $name = null,
        public ?StockTypeEnum $type = null,
        public ?string $location = null,
        public ?string $responsible = null,
        public ?float $capacity = null,
        public ?StockStatusEnum $status = null,
        public ?string $notes = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $type   = $data['type'] ?? null;
        $status = $data['status'] ?? null;

        return new self(
            companyId:          (string) ($data['company_id'] ?? $data['companyId']),
            supplyId:           isset($data['supply_id']) ? (string) $data['supply_id']
                              : (isset($data['supplyId']) ? (string) $data['supplyId'] : null),
            quantity:           (float)  ($data['quantity'] ?? 0),
            unit:               (string) ($data['unit'] ?? 'un'),
            unitPrice:          (float)  ($data['unit_price'] ?? $data['unitPrice'] ?? 0),
            totalCost:          (float)  ($data['total_cost'] ?? $data['totalCost'] ?? 0),
            minimumStock:       (float)  ($data['minimum_stock'] ?? $data['minimumStock'] ?? 0),
            withdrawalQuantity: (float)  ($data['withdrawal_quantity'] ?? $data['withdrawalQuantity'] ?? 0),
            supplierId:         isset($data['supplier_id']) ? (string) $data['supplier_id']
                              : (isset($data['supplierId']) ? (string) $data['supplierId'] : null),
            referenceId:        isset($data['reference_id']) ? (string) $data['reference_id']
                              : (isset($data['referenceId']) ? (string) $data['referenceId'] : null),
            code:               isset($data['code']) ? (string) $data['code'] : null,
            name:               isset($data['name']) ? (string) $data['name'] : null,
            type:               $type !== null
                              ? ($type instanceof StockTypeEnum ? $type : StockTypeEnum::from((string) $type))
                              : null,
            location:           isset($data['location']) ? (string) $data['location'] : null,
            responsible:        isset($data['responsible']) ? (string) $data['responsible'] : null,
            capacity:           isset($data['capacity']) ? (float) $data['capacity'] : null,
            status:             $status !== null
                              ? ($status instanceof StockStatusEnum ? $status : StockStatusEnum::from((string) $status))
                              : null,
            notes:              isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }
}
