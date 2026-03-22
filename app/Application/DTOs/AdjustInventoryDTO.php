<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class AdjustInventoryDTO
{
    public function __construct(
        public readonly string  $stockId,
        public readonly float   $newPhysicalQuantity,
        public readonly string  $userId,
        public readonly ?string $reason = null,
    ) {}

    /**
     * @param array{
     *     stock_id: string,
     *     new_physical_quantity: float,
     *     user_id: string,
     *     reason?: string|null,
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            stockId:             (string) ($data['stock_id'] ?? $data['stockId']),
            newPhysicalQuantity: (float)  ($data['new_physical_quantity'] ?? $data['newPhysicalQuantity']),
            userId:              (string) ($data['user_id'] ?? $data['userId']),
            reason:              isset($data['reason']) ? (string) $data['reason'] : null,
        );
    }
}