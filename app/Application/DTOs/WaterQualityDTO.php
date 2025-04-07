<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class WaterQualityDTO
{
    /**
     * @param array{id?: string|null, name?: string|null}|null $tank
     */
    public function __construct(
        public string $id,
        public float $ph,
        public float $oxygen,
        public float $temperature,
        public float $ammonia,
        public ?array $tank = null,
        public ?string $analysisDate = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            ph: (float) $data['ph'],
            oxygen: (float) $data['oxygen'],
            temperature: (float) $data['temperature'],
            ammonia: (float) $data['ammonia'],
            tank: isset($data['tank']) ? [
                'id'   => $data['tank']['id'] ?? null,
                'name' => $data['tank']['name'] ?? null,
            ] : null,
            analysisDate: $data['analysis_date'] ?? null,
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
            'id'           => $this->id,
            'ph'           => $this->ph,
            'oxygen'       => $this->oxygen,
            'temperature'  => $this->temperature,
            'ammonia'      => $this->ammonia,
            'tank'         => $this->tank,
            'analysisDate' => $this->analysisDate,
            'createdAt'    => $this->createdAt,
            'updatedAt'    => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->id === '' || $this->id === '0';
    }
}
