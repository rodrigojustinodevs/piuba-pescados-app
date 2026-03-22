<?php

declare(strict_types=1);

namespace App\Application\DTOs;

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
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest
     */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId:          (string) ($data['company_id'] ?? $data['companyId']),
            supplyId:           isset($data['supply_id']) ? (string) $data['supply_id']
                              : (isset($data['supplyId']) ? (string) $data['supplyId'] : null),
            quantity:           (float)   $data['quantity'],
            unit:               (string)  $data['unit'],
            unitPrice:          (float)  ($data['unit_price'] ?? $data['unitPrice'] ?? 0),
            totalCost:          (float)  ($data['total_cost'] ?? $data['totalCost'] ?? 0),
            minimumStock:       (float)  ($data['minimum_stock'] ?? $data['minimumStock'] ?? 0),
            withdrawalQuantity: (float)  ($data['withdrawal_quantity'] ?? $data['withdrawalQuantity'] ?? 0),
            supplierId:         isset($data['supplier_id']) ? (string) $data['supplier_id']
                              : (isset($data['supplierId']) ? (string) $data['supplierId'] : null),
            referenceId:        isset($data['reference_id']) ? (string) $data['reference_id']
                              : (isset($data['referenceId']) ? (string) $data['referenceId'] : null),
        );
    }
}
