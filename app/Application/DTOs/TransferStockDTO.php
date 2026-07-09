<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class TransferStockDTO
{
    public function __construct(
        public string $sourceStockId,
        public string $destinationStockId,
        public string $supplyId,
        public string $userId,
        public float $quantity,
        public ?string $reason = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, string $userId): self
    {
        return new self(
            sourceStockId:      (string) ($data['source_stock_id'] ?? $data['sourceStockId']),
            destinationStockId: (string) ($data['destination_stock_id'] ?? $data['destinationStockId']),
            supplyId:           (string) ($data['supply_id'] ?? $data['supplyId']),
            userId:             $userId,
            quantity:           (float) $data['quantity'],
            reason:             isset($data['reason']) ? (string) $data['reason'] : null,
        );
    }
}
