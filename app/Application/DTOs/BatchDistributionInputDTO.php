<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class BatchDistributionInputDTO
{
    /**
     * @param array<int, array{tankId: string, quantity: int, averageWeight: float}> $distribution
     */
    public function __construct(
        public string $supplierId,
        public string $companyId,
        public float $totalCost,
        public string $entryDate,
        public string $species,
        public string $cultivation,
        public array $distribution,
        public ?string $notes = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $companyId = (string) ($data['company_id'] ?? $data['companyId'] ?? '');

        return new self(
            supplierId: (string) $data['supplierId'],
            companyId: $companyId,
            totalCost: (float) $data['totalCost'],
            entryDate: (string) $data['entryDate'],
            species: (string) $data['species'],
            cultivation: (string) $data['cultivation'],
            distribution: $data['distribution'],
            notes: $data['notes'] ?? null,
        );
    }

    public function getTotalQuantity(): int
    {
        return array_sum(array_column($this->distribution, 'quantity'));
    }
}
