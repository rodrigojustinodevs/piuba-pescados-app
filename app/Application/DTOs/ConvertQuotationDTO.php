<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class ConvertQuotationDTO
{
    public function __construct(
        public string $expectedDeliveryDate,
        public string $financialCategoryId,
        public string $expectedPaymentDate,
        public bool $needsInvoice,
        public ?string $notes = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            expectedDeliveryDate: (string) ($data['expected_delivery_date'] ?? $data['expectedDeliveryDate'] ?? ''),
            financialCategoryId:  (string) ($data['financial_category_id'] ?? $data['financialCategoryId'] ?? ''),
            expectedPaymentDate:  (string) ($data['expected_payment_date'] ?? $data['expectedPaymentDate'] ?? ''),
            needsInvoice:         (bool)   ($data['needs_invoice'] ?? $data['needsInvoice'] ?? false),
            notes:                isset($data['notes']) && is_string($data['notes']) ? $data['notes'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistenceOverrides(): array
    {
        $overrides = [
            'expected_delivery_date' => $this->expectedDeliveryDate,
        ];

        if ($this->notes !== null) {
            $overrides['notes'] = $this->notes;
        }

        return $overrides;
    }
}
