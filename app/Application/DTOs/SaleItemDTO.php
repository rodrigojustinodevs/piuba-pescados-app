<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class SaleItemDTO
{
    public function __construct(
        public string $batchId,
        public string $stockingId,
        public float $totalWeight,
        public float $pricePerKg,
        public bool $isHarvestTotal = false,
        public ?string $productName = null,
        public ?string $species = null,
        public ?string $category = null,
        public ?string $notes = null,
    ) {
    }

    public function subtotal(): float
    {
        return round($this->totalWeight * $this->pricePerKg, 2);
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            batchId:        (string) ($data['batch_id'] ?? $data['batchId'] ?? ''),
            stockingId:     (string) ($data['stocking_id'] ?? $data['stockingId'] ?? ''),
            totalWeight:    (float)  ($data['total_weight'] ?? $data['totalWeight'] ?? 0),
            pricePerKg:     (float)  ($data['price_per_kg'] ?? $data['pricePerKg'] ?? 0),
            isHarvestTotal: (bool)   ($data['is_total_harvest'] ?? $data['isHarvestTotal'] ?? false),
            productName:    isset($data['product_name']) ? (string) $data['product_name']
                          : (isset($data['productName']) ? (string) $data['productName'] : null),
            species:        isset($data['species']) ? (string) $data['species'] : null,
            category:       isset($data['category']) ? (string) $data['category'] : null,
            notes:          isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }
}
