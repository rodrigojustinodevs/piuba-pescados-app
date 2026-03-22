<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\PurchaseStatus;
use App\Application\DTOs\PurchaseItemDTO;

final class PurchaseDTO
{
    /** @param PurchaseItemDTO[] $items */
    public function __construct(
        public readonly string         $companyId,
        public readonly string         $supplierId,
        public readonly string         $purchaseDate,
        public readonly PurchaseStatus $status,
        public readonly array          $items,
        public readonly ?string        $invoiceNumber = null,
        public readonly ?string        $receivedAt    = null,
    ) {}

    /**
     * @param array{
     *     company_id: string,
     *     supplier_id: string,
     *     purchase_date: string,
     *     status?: string|null,
     *     items: array<array-key, mixed>,
     *     invoice_number?: string|null,
     *     received_at?: string|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        $items = array_map(
            static fn (array $item): PurchaseItemDTO => PurchaseItemDTO::fromArray($item),
            $data['items'] ?? [],
        );

        return new self(
            companyId:     (string) $data['company_id'],
            supplierId:    (string) ($data['supplier_id'] ?? $data['supplierId']),
            purchaseDate:  (string) ($data['purchase_date'] ?? $data['purchaseDate']),
            status:        PurchaseStatus::from($data['status'] ?? PurchaseStatus::DRAFT->value),
            items:         $items,
            invoiceNumber: $data['invoice_number'] ?? $data['invoiceNumber'] ?? null,
            receivedAt:    $data['received_at'] ?? null,
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