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
        public float $totalCost,
        public string $entryDate,
        public string $species,
        public string $cultivation,
        public array $distribution,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            supplierId:  $data['supplierId'],
            totalCost:   (float) $data['totalCost'],
            entryDate:    $data['entryDate'],
            species:      $data['species'],
            cultivation:  $data['cultivation'],
            distribution: $data['distribution'],
            notes:        $data['notes'] ?? null,
        );
    }

    public function getTotalQuantity(): int
    {
        return array_sum(array_column($this->distribution, 'quantity'));
    }
}