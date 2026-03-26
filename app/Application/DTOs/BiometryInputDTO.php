<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class BiometryInputDTO
{
    public function __construct(
        public string $batchId,
        public string $biometryDate,
        public float $averageWeight,
        public float $fcr = 0.0,
        public ?float $sampleWeight = null,
        public ?int $sampleQuantity = null,
        public ?float $biomassEstimated = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            batchId:          (string) ($data['batch_id'] ?? $data['batchId'] ?? ''),
            biometryDate:     (string) ($data['biometry_date'] ?? $data['biometryDate'] ?? ''),
            averageWeight:    (float) ($data['average_weight'] ?? $data['averageWeight'] ?? 0),
            fcr:              (float) ($data['fcr'] ?? 0),
            sampleWeight: isset($data['sample_weight']) || isset($data['sampleWeight'])
                ? (float) ($data['sample_weight'] ?? $data['sampleWeight'])
                : null,
            sampleQuantity: isset($data['sample_quantity']) || isset($data['sampleQuantity'])
                ? (int) ($data['sample_quantity'] ?? $data['sampleQuantity'])
                : null,
            biomassEstimated: isset($data['biomass_estimated']) || isset($data['biomassEstimated'])
                ? (float) ($data['biomass_estimated'] ?? $data['biomassEstimated'])
                : null,
        );
    }

    /** @return array<string, mixed> */
    public function toPersistence(): array
    {
        return array_filter([
            'batch_id'          => $this->batchId,
            'biometry_date'     => $this->biometryDate,
            'average_weight'    => $this->averageWeight,
            'fcr'               => $this->fcr,
            'sample_weight'     => $this->sampleWeight,
            'sample_quantity'   => $this->sampleQuantity,
            'biomass_estimated' => $this->biomassEstimated,
        ], static fn (string | int | float | null $v): bool => $v !== null);
    }
}
