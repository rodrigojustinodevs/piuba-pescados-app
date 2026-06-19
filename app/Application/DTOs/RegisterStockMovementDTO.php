<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\StockMovementTypeEnum;

final readonly class RegisterStockMovementDTO
{
    public function __construct(
        public string $stockId,
        public string $supplyId,
        public string $userId,
        public StockMovementTypeEnum $type,
        public float $quantity,
        public ?string $reason = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, string $userId): self
    {
        $type = $data['type'] ?? null;

        return new self(
            stockId:  (string) ($data['stock_id'] ?? $data['stockId']),
            supplyId: (string) ($data['supply_id'] ?? $data['supplyId']),
            userId:   $userId,
            type:     $type instanceof StockMovementTypeEnum
                      ? $type
                      : StockMovementTypeEnum::from((string) $type),
            quantity: (float) $data['quantity'],
            reason:   isset($data['reason']) ? (string) $data['reason'] : null,
        );
    }
}
