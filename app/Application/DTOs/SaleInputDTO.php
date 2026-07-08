<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\PaymentMethod;
use App\Domain\Enums\SaleStatus;

final readonly class SaleInputDTO
{
    /**
     * @param array<int, SaleItemDTO> $items
     */
    public function __construct(
        public string $companyId,
        public string $clientId,
        public string $saleDate,
        public array $items,
        public ?string $financialCategoryId = null,
        public ?string $responsibleUserId = null,
        public SaleStatus $status = SaleStatus::PENDING,
        public ?string $notes = null,
        public bool $requiresInvoice = false,
        public float $discount = 0.0,
        public float $shipping = 0.0,
        public float $taxes = 0.0,
        public ?string $dueDate = null,
        public ?string $paidAt = null,
        public bool $needsInvoice = false,
        public ?PaymentMethod $paymentMethod = null,
        public ?string $invoiceNumber = null,
    ) {
    }

    public function totalRevenue(): float
    {
        $subtotals = array_map(static fn (SaleItemDTO $item): float => $item->subtotal(), $this->items);

        return round((float) array_sum($subtotals), 2);
    }

    public function firstItem(): SaleItemDTO
    {
        return $this->items[0];
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $paymentMethodRaw = $data['payment_method'] ?? $data['paymentMethod'] ?? null;

        // Suporta formato legado (campos diretos) e formato novo (items[])
        if (! isset($data['items']) || ! is_array($data['items'])) {
            $data['items'] = [[
                'batch_id'         => $data['batch_id'] ?? $data['batchId'] ?? '',
                'stocking_id'      => $data['stocking_id'] ?? $data['stockingId'] ?? '',
                'total_weight'     => $data['total_weight'] ?? $data['totalWeight'] ?? 0,
                'price_per_kg'     => $data['price_per_kg'] ?? $data['pricePerKg'] ?? 0,
                'is_total_harvest' => $data['is_total_harvest'] ?? $data['isHarvestTotal'] ?? false,
            ]];
        }

        $items = array_map(
            SaleItemDTO::fromArray(...),
            $data['items'],
        );

        return new self(
            companyId:           (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            clientId:            (string) ($data['client_id'] ?? $data['clientId'] ?? ''),
            saleDate:            (string) ($data['sale_date'] ?? $data['saleDate'] ?? ''),
            items:               $items,
            financialCategoryId: isset($data['financial_category_id']) ? (string) $data['financial_category_id']
                               : (isset($data['financialCategoryId']) ? (string) $data['financialCategoryId'] : null),
            responsibleUserId:   isset($data['responsible_user_id']) ? (string) $data['responsible_user_id']
                               : (isset($data['responsibleUserId']) ? (string) $data['responsibleUserId'] : null),
            status:              isset($data['status'])
                               ? SaleStatus::from((string) $data['status'])
                               : SaleStatus::PENDING,
            notes:               isset($data['notes']) ? (string) $data['notes'] : null,
            requiresInvoice:     (bool) ($data['requires_invoice'] ?? $data['requiresInvoice'] ?? false),
            discount:            (float) ($data['discount'] ?? 0),
            shipping:            (float) ($data['shipping'] ?? $data['freight'] ?? 0),
            taxes:               (float) ($data['taxes'] ?? 0),
            dueDate:             isset($data['due_date']) ? (string) $data['due_date']
                               : (isset($data['dueDate']) ? (string) $data['dueDate'] : null),
            paidAt:              isset($data['paid_at']) ? (string) $data['paid_at']
                               : (isset($data['paidAt']) ? (string) $data['paidAt'] : null),
            needsInvoice:        (bool) ($data['needs_invoice'] ?? $data['needsInvoice'] ?? false),
            paymentMethod:       $paymentMethodRaw !== null ? PaymentMethod::from((string) $paymentMethodRaw) : null,
            invoiceNumber:       isset($data['invoice_number']) ? (string) $data['invoice_number']
                               : (isset($data['invoiceNumber']) ? (string) $data['invoiceNumber']
                               : (isset($data['numberNf']) ? (string) $data['numberNf'] : null)),
        );
    }
}
