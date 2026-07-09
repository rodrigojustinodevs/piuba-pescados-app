<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\PaymentMethod;

final readonly class SalePaymentDTO
{
    public function __construct(
        public float $amount,
        public PaymentMethod $paymentMethod,
        public string $paymentDate,
        public ?string $reference = null,
        public ?string $notes = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            amount:        (float) ($data['amount'] ?? 0),
            paymentMethod: PaymentMethod::from((string) ($data['payment_method'] ?? $data['paymentMethod'] ?? '')),
            paymentDate:   (string) ($data['payment_date'] ?? $data['paymentDate'] ?? ''),
            reference:     isset($data['reference']) ? (string) $data['reference'] : null,
            notes:         isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'amount'         => $this->amount,
            'payment_method' => $this->paymentMethod->value,
            'payment_date'   => $this->paymentDate,
            'reference'      => $this->reference,
            'notes'          => $this->notes,
        ];
    }
}
