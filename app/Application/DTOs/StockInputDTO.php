<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class StockInputDTO
{
    public function __construct(
        public readonly string  $companyId,
        public readonly string  $supplyId,
        public readonly float   $quantity,
        public readonly string  $unit,
        public readonly float   $unitPrice,
        public readonly float   $totalCost,
        public readonly float   $minimumStock,
        public readonly float   $withdrawalQuantity,
        public readonly ?string $supplierId  = null,
        public readonly ?string $referenceId = null,
    ) {}

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest
     */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId:          (string) ($data['company_id']          ?? $data['companyId']),
            supplyId:           (string) ($data['supply_id']           ?? $data['supplyId']),
            quantity:           (float)   $data['quantity'],
            unit:               (string)  $data['unit'],
            unitPrice:          (float)  ($data['unit_price']          ?? $data['unitPrice']   ?? 0),
            totalCost:          (float)  ($data['total_cost']          ?? $data['totalCost']   ?? 0),
            minimumStock:       (float)  ($data['minimum_stock']       ?? $data['minimumStock'] ?? 0),
            withdrawalQuantity: (float)  ($data['withdrawal_quantity'] ?? $data['withdrawalQuantity'] ?? 0),
            supplierId:         isset($data['supplier_id']) ? (string) $data['supplier_id']
                              : (isset($data['supplierId']) ? (string) $data['supplierId'] : null),
            referenceId:        isset($data['reference_id']) ? (string) $data['reference_id']
                              : (isset($data['referenceId']) ? (string) $data['referenceId'] : null),
        );
    }
}