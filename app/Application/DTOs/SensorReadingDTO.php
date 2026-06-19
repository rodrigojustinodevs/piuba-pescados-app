<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class SensorReadingDTO
{
    public function __construct(
        public string $sensorId,
        public string $companyId,
        public float $value,
        public string $unit,
        public string $measuredAt,
        public string $type,
        public ?string $notes = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sensorId:   (string) ($data['sensor_id'] ?? $data['sensorId'] ?? ''),
            companyId:  (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            value:      (float)  ($data['value'] ?? 0),
            unit:       (string) ($data['unit'] ?? ''),
            measuredAt: (string) ($data['measured_at'] ?? $data['measuredAt'] ?? ''),
            type:       (string) ($data['type'] ?? ''),
            notes:      isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(): array
    {
        return [
            'sensor_id'   => $this->sensorId,
            'company_id'  => $this->companyId,
            'value'       => $this->value,
            'unit'        => $this->unit,
            'measured_at' => $this->measuredAt,
            'type'        => $this->type,
            'notes'       => $this->notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toUpdateAttributes(): array
    {
        return array_filter(
            [
                'sensor_id'   => $this->sensorId,
                'company_id'  => $this->companyId,
                'value'       => $this->value,
                'unit'        => $this->unit,
                'measured_at' => $this->measuredAt,
                'type'        => $this->type,
                'notes'       => $this->notes,
            ],
            static fn (float|string|null $v): bool => $v !== null && $v !== ''
        );
    }
}
