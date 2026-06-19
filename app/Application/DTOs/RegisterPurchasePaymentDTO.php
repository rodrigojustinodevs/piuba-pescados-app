<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class RegisterPurchasePaymentDTO
{
    public function __construct(
        public string $purchaseId,
        public string $paymentDate,
        public float $amount,
        public string $paymentMethod,
        public ?string $reference,
        public ?string $notes,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(string $purchaseId, array $data): self
    {
        return new self(
            purchaseId:    $purchaseId,
            paymentDate:   (string) $data['payment_date'],
            amount:        (float) $data['amount'],
            paymentMethod: (string) $data['payment_method'],
            reference:     isset($data['reference']) ? (string) $data['reference'] : null,
            notes:         isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }
}
