<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\PurchaseStatus;

final readonly class PurchaseDTO
{
    /** @param PurchaseItemDTO[] $items */
    public function __construct(
        public string $companyId,
        public string $supplierId,
        public string $purchaseDate,
        public PurchaseStatus $status,
        public array $items,
        public ?string $invoiceNumber = null,
        public ?string $receivedAt = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $items = array_map(
            PurchaseItemDTO::fromArray(...),
            $data['items'] ?? [],
        );

        return new self(
            companyId:     (string) ($data['company_id'] ?? ''),
            supplierId:    (string) ($data['supplier_id'] ?? $data['supplierId'] ?? ''),
            purchaseDate:  (string) ($data['purchase_date'] ?? $data['purchaseDate'] ?? ''),
            status:        PurchaseStatus::from($data['status'] ?? PurchaseStatus::DRAFT->value),
            items:         $items,
            invoiceNumber: isset($data['invoice_number']) ? (string) $data['invoice_number']
                : (isset($data['invoiceNumber']) ? (string) $data['invoiceNumber'] : null),
            receivedAt:    isset($data['received_at']) ? (string) $data['received_at'] : null,
        );
    }

    public function totalPrice(): float
    {
        return round(
            array_reduce(
                $this->items,
                static fn (float $carry, PurchaseItemDTO $item): float => $carry + $item->resolvedTotalPrice(),
                0.0,
            ),
            2,
        );
    }
}
