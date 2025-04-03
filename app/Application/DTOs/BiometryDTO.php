<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class BiometryDTO
{
    public function __construct(
        public string $id,
        public string $batcheId,
        public float $averageWeight,
        public float $fcr,
        public ?string $biometryDate = null,
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
            batcheId: $data['batche_id'],
            biometryDate: $data['biometry_date'] ?? null,
            averageWeight: (float) $data['average_weight'],
            fcr: (float) $data['fcr'],
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
            'batcheId'      => $this->batcheId,
            'biometryDate'  => $this->biometryDate,
            'averageWeight' => $this->averageWeight,
            'fcr'           => $this->fcr,
            'createdAt'     => $this->createdAt,
            'updatedAt'     => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
               ($this->batcheId === '' || $this->batcheId === '0') &&
               $this->averageWeight === 0.0 &&
               $this->fcr === 0.0;
    }
}
