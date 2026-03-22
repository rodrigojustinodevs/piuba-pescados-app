<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class AdjustInventoryDTO
{
    public function __construct(
        public string $stockId,
        public float $newPhysicalQuantity,
        public string $userId,
        public ?string $reason = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            stockId:             (string) ($data['stock_id'] ?? $data['stockId'] ?? ''),
            newPhysicalQuantity: (float)  ($data['new_physical_quantity'] ?? $data['newPhysicalQuantity'] ?? 0),
            userId:              (string) ($data['user_id'] ?? $data['userId'] ?? ''),
            reason:              isset($data['reason']) ? (string) $data['reason'] : null,
        );
    }
}
