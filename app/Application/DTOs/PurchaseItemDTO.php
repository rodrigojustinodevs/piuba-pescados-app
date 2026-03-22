<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class PurchaseItemDTO
{
    public function __construct(
        public readonly string  $supplyId,
        public readonly float   $quantity,
        public readonly string  $unit,
        public readonly float   $unitPrice,
        public readonly ?string $id         = null,
        public readonly ?float  $totalPrice = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $quantity  = (float) ($data['quantity']  ?? 0);
        $unitPrice = (float) ($data['unitPrice'] ?? $data['unit_price'] ?? 0);

        return new self(
            supplyId:   (string) ($data['supplyId'] ?? $data['supply_id']),
            quantity:   $quantity,
            unit:       (string)  $data['unit'],
            unitPrice:  $unitPrice,
            id:         isset($data['id']) ? (string) $data['id'] : null,
            totalPrice: isset($data['total_price']) ? (float) $data['total_price'] : null,
        );
    }

    public function resolvedTotalPrice(): float
    {
        return $this->totalPrice ?? round($this->quantity * $this->unitPrice, 2);
    }

    /** @return array<string, mixed> */
    public function toPersistence(): array
    {
        return [
            'supply_id'   => $this->supplyId,
            'quantity'    => $this->quantity,
            'unit'        => $this->unit,
            'unit_price'  => $this->unitPrice,
            'total_price' => $this->resolvedTotalPrice(),
        ];
    }
}