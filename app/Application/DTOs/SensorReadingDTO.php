<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class SensorReadingDTO
{
    /**
     * @param array{id?: string|null, sensorType?: string|null}|null $sensor
     */
    public function __construct(
        public string $id,
        public float $value,
        public ?string $readingDate = null,
        public ?array $sensor = null,
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
            value: (float) $data['value'],
            readingDate: $data['reading_date'] ?? null,
            sensor: isset($data['sensor']) ? [
                'id'         => $data['sensor']['id'] ?? null,
                'sensorType' => $data['sensor']['sensor_type'] ?? null,
            ] : null,
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
            'id'          => $this->id,
            'value'       => $this->value,
            'readingDate' => $this->readingDate,
            'sensor'      => $this->sensor,
            'createdAt'   => $this->createdAt,
            'updatedAt'   => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->id === '' || $this->id === '0';
    }
}
