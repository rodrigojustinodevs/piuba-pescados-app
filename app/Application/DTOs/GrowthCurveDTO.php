<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class GrowthCurveDTO
{
    public function __construct(
        public string $id,
        public float $averageWeight,
        public string $batcheId,
        public ?string $createdAt = null,
        public ?string $updatedAt = null
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            averageWeight: (float) $data['average_weight'],
            batcheId: $data['batche_id'],
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'averageWeight' => $this->averageWeight,
            'batcheId'      => $this->batcheId,
            'createdAt'     => $this->createdAt,
            'updatedAt'     => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
            $this->averageWeight === 0.0;
    }
}
