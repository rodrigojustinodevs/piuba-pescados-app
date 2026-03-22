<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class StockAdjustmentDTO
{
    public function __construct(
        public readonly float   $physicalQuantity,
        public readonly ?string $reason = null,
    ) {}

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest
     */
    public static function fromArray(array $data): self
    {
        return new self(
            physicalQuantity: (float)   ($data['new_physical_quantity'] ?? $data['physicalQuantity'] ?? $data['physical_quantity']),
            reason:           isset($data['reason']) ? (string) $data['reason'] : null
        );
    }
}