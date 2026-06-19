<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\PurchasePaymentMethod;
use App\Domain\Enums\PurchasePaymentStatus;
use App\Domain\Enums\PurchaseStatus;

final readonly class PurchaseDTO
{
    /** @param PurchaseItemDTO[] $items */
    public function __construct(
        public string $companyId,
        public string $supplierId,
        public string $orderDate,
        public PurchaseStatus $status,
        public PurchasePaymentStatus $paymentStatus,
        public array $items,
        public string $code,
        public ?PurchasePaymentMethod $paymentMethod = null,
        public ?string $invoiceNumber = null,
        public ?string $expectedDate = null,
        public ?string $receivedDate = null,
        public float $freight = 0.0,
        public float $otherCosts = 0.0,
        public ?string $notes = null,
        public ?string $responsible = null,
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

        $paymentMethod = $data['payment_method'] ?? $data['paymentMethod'] ?? null;

        return new self(
            companyId:     (string) ($data['company_id'] ?? ''),
            supplierId:    (string) ($data['supplier_id'] ?? $data['supplierId'] ?? ''),
            orderDate:     (string) ($data['order_date'] ?? $data['orderDate'] ?? ''),
            status:        PurchaseStatus::from($data['status'] ?? PurchaseStatus::DRAFT->value),
            paymentStatus: PurchasePaymentStatus::from(
                $data['payment_status'] ?? $data['paymentStatus'] ?? PurchasePaymentStatus::PENDING->value
            ),
            items:         $items,
            code:          (string) ($data['code'] ?? ''),
            paymentMethod: $paymentMethod !== null
                ? PurchasePaymentMethod::from((string) $paymentMethod)
                : null,
            invoiceNumber: isset($data['invoice_number']) ? (string) $data['invoice_number']
                : (isset($data['invoiceNumber']) ? (string) $data['invoiceNumber'] : null),
            expectedDate:  isset($data['expected_date']) ? (string) $data['expected_date']
                : (isset($data['expectedDate']) ? (string) $data['expectedDate'] : null),
            receivedDate:  isset($data['received_date']) ? (string) $data['received_date']
                : (isset($data['receivedDate']) ? (string) $data['receivedDate'] : null),
            freight:       (float) ($data['freight'] ?? 0),
            otherCosts:    (float) ($data['other_costs'] ?? $data['otherCosts'] ?? 0),
            notes:         isset($data['notes']) ? (string) $data['notes'] : null,
            responsible:   isset($data['responsible']) ? (string) $data['responsible'] : null,
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
