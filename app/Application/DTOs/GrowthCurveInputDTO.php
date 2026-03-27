<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class GrowthCurveInputDTO
{
    public function __construct(
        public string $batchId,
        public float $averageWeight,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            batchId:       (string) ($data['batch_id'] ?? $data['batchId'] ?? ''),
            averageWeight: (float) ($data['average_weight'] ?? $data['averageWeight'] ?? 0),
        );
    }

    /**
     * @return array<string, float|string>
     */
    public function toPersistence(): array
    {
        return [
            'batch_id'       => $this->batchId,
            'average_weight' => $this->averageWeight,
        ];
    }
}
