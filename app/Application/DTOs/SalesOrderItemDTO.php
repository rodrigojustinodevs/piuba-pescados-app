<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class SalesOrderItemDTO
{
    public function __construct(
        public string $stockingId,
        public float $quantity,
        public float $unitPrice,
        public string $measureUnit,
        public ?string $id = null,
    ) {
    }

    public function subtotal(): float
    {
        return round($this->quantity * $this->unitPrice, 2);
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            stockingId:     (string) ($row['stocking_id'] ?? $row['stockingId'] ?? ''),
            quantity:    (float)  ($row['quantity'] ?? 0),
            unitPrice:   (float)  ($row['unit_price'] ?? $row['unitPrice'] ?? 0),
            measureUnit: (string) ($row['measure_unit'] ?? $row['measureUnit'] ?? 'kg'),
            id:          (string) ($row['id'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(string $salesOrderId): array
    {
        return [
            'sales_order_id' => $salesOrderId,
            'stocking_id'    => $this->stockingId,
            'quantity'       => $this->quantity,
            'unit_price'     => $this->unitPrice,
            'subtotal'       => $this->subtotal(),
            'measure_unit'   => $this->measureUnit,
        ];
    }
}
