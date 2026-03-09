<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class BiometryDTO
{
    public function __construct(
        public string $id,
        public string $batchId,
        public float $averageWeight,
        public float $fcr,
        public ?string $biometryDate = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?float $sampleWeight = null,
        public ?int $sampleQuantity = null,
        public ?float $biomassEstimated = null,
        public ?float $densityAtTime = null,
        public ?float $recommendedRation = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            batchId: $data['batch_id'],
            averageWeight: (float) $data['average_weight'],
            fcr: (float) $data['fcr'],
            biometryDate: $data['biometry_date'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
            sampleWeight: isset($data['sample_weight']) ? (float) $data['sample_weight'] : null,
            sampleQuantity: isset($data['sample_quantity']) ? (int) $data['sample_quantity'] : null,
            biomassEstimated: isset($data['biomass_estimated']) ? (float) $data['biomass_estimated'] : null,
            densityAtTime: isset($data['density_at_time']) ? (float) $data['density_at_time'] : null,
            recommendedRation: isset($data['recommended_ration']) ? (float) $data['recommended_ration'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $arr = [
            'id'            => $this->id,
            'batchId'       => $this->batchId,
            'biometryDate'  => $this->biometryDate,
            'averageWeight' => $this->averageWeight,
            'fcr'           => $this->fcr,
            'createdAt'     => $this->createdAt,
            'updatedAt'     => $this->updatedAt,
        ];

        if ($this->sampleWeight !== null) {
            $arr['sampleWeight'] = $this->sampleWeight;
        }

        if ($this->sampleQuantity !== null) {
            $arr['sampleQuantity'] = $this->sampleQuantity;
        }

        if ($this->biomassEstimated !== null) {
            $arr['biomassEstimated'] = $this->biomassEstimated;
        }

        if ($this->densityAtTime !== null) {
            $arr['densityAtTime'] = $this->densityAtTime;
        }

        if ($this->recommendedRation !== null) {
            $arr['recommendedRation'] = $this->recommendedRation;
        }

        return $arr;
    }

    public function isEmpty(): bool
    {
        return ($this->id === '' || $this->id === '0') &&
               ($this->batchId === '' || $this->batchId === '0') &&
               $this->averageWeight === 0.0 &&
               $this->fcr === 0.0;
    }
}
