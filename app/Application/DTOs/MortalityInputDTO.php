<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Enums\MortalityCause;
use App\Domain\Enums\MortalitySeverity;

final readonly class MortalityInputDTO
{
    public function __construct(
        public string $batchId,
        public string $mortalityDate,
        public int $quantity,
        public MortalityCause $cause,
        public ?MortalitySeverity $severity,
        public ?string $description = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $severityRaw = $data['severity'] ?? '';

        return new self(
            batchId:       (string) ($data['batch_id'] ?? $data['batchId'] ?? ''),
            mortalityDate: (string) ($data['mortality_date'] ?? $data['mortalityDate'] ?? ''),
            quantity:      (int) ($data['quantity'] ?? 0),
            cause:         MortalityCause::from((string) ($data['cause'] ?? '')),
            severity:      is_string($severityRaw) && $severityRaw !== ''
                ? MortalitySeverity::tryFrom($severityRaw)
                : null,
            description:   isset($data['description']) ? (string) $data['description'] : null,
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
            'cause'          => $this->cause->value,
            'description'    => $this->description,
            'severity'       => $this->severity?->value,
        ];
    }
}
