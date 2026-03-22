<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class StockAdjustmentDTO
{
    public function __construct(
        public float $physicalQuantity,
        public ?string $reason = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest
     */
    public static function fromArray(array $data): self
    {
        $qty = $data['new_physical_quantity']
            ?? $data['physicalQuantity']
            ?? $data['physical_quantity'];

        return new self(
            physicalQuantity: (float) $qty,
            reason:           isset($data['reason']) ? (string) $data['reason'] : null
        );
    }
}
