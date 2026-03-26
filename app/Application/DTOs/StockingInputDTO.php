<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class StockingInputDTO
{
    public function __construct(
        public string $batchId,
        public string $stockingDate,
        public int $quantity,
        public float $averageWeight,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            batchId:       (string) ($data['batch_id'] ?? $data['batchId'] ?? ''),
            stockingDate:  (string) ($data['stocking_date'] ?? $data['stockingDate'] ?? ''),
            quantity:      (int) ($data['quantity'] ?? 0),
            averageWeight: (float) ($data['average_weight'] ?? $data['averageWeight'] ?? 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(): array
    {
        return [
            'batch_id'       => $this->batchId,
            'stocking_date'  => $this->stockingDate,
            'quantity'       => $this->quantity,
            'average_weight' => $this->averageWeight,
        ];
    }
}
