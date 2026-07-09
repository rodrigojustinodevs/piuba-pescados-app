<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class ReceivePurchaseDTO
{
    /**
     * @param array<int, array{purchase_item_id: string, received_quantity: float}> $items
     */
    public function __construct(
        public string $purchaseId,
        public array $items,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(string $purchaseId, array $data): self
    {
        return new self(
            purchaseId: $purchaseId,
            items:      $data['items'],
        );
    }
}
