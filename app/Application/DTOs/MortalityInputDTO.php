<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class MortalityInputDTO
{
    public function __construct(
        public string $batchId,
        public string $mortalityDate,
        public int $quantity,
        public string $cause,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            batchId:       (string) ($data['batch_id'] ?? $data['batchId'] ?? ''),
            mortalityDate: (string) ($data['mortality_date'] ?? $data['mortalityDate'] ?? ''),
            quantity:      (int) ($data['quantity'] ?? 0),
            cause:         (string) ($data['cause'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(): array
    {
        return [
            'batch_id'       => $this->batchId,
            'mortality_date' => $this->mortalityDate,
            'quantity'       => $this->quantity,
            'cause'          => $this->cause,
        ];
    }
}
