<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\Models\SensorReading;
use Carbon\CarbonInterface;

final readonly class SensorReadingDTO
{
    /**
     * @param array{
     *     id: string,
     *     sensorType?: string,
     *     status?: string,
     *     tank?: array{id: string, name: string}|null
     * }|null $sensor
     */
    public function __construct(
        public string $sensorId,
        public string $companyId,
        public float $value,
        public string $unit,
        public string $measuredAt,
        public ?string $notes = null,
        public string $id = '',
        public ?array $sensor = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public static function fromModel(SensorReading $reading): self
    {
        $measured    = $reading->measured_at;
        $measuredStr = $measured instanceof CarbonInterface
            ? $measured->toIso8601String()
            : (string) $measured;

        $sensorPayload = null;

        if ($reading->relationLoaded('sensor') && $reading->sensor !== null) {
            $s    = $reading->sensor;
            $tank = null;

            if ($s->relationLoaded('tank') && $s->tank !== null) {
                $tank = ['id' => $s->tank->id, 'name' => $s->tank->name];
            }

            $sensorPayload = [
                'id'         => $s->id,
                'sensorType' => $s->sensor_type,
                'status'     => $s->status,
                'tank'       => $tank,
            ];
        }

        return new self(
            sensorId:   $reading->sensor_id,
            companyId:  $reading->company_id,
            value:      (float) $reading->value,
            unit:       $reading->unit,
            measuredAt: $measuredStr,
            notes:      $reading->notes,
            id:         $reading->id,
            sensor:     $sensorPayload,
            createdAt:  $reading->created_at?->toDateTimeString(),
            updatedAt:  $reading->updated_at?->toDateTimeString(),
        );
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            sensorId:   (string) ($data['sensor_id'] ?? $data['sensorId'] ?? ''),
            companyId:  (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            value:      (float) ($data['value'] ?? 0),
            unit:       (string) ($data['unit'] ?? ''),
            measuredAt: (string) ($data['measured_at'] ?? $data['measuredAt'] ?? ''),
            notes:      isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'sensorId'   => $this->sensorId,
            'companyId'  => $this->companyId,
            'value'      => $this->value,
            'unit'       => $this->unit,
            'measuredAt' => $this->measuredAt,
            'notes'      => $this->notes,
            'sensor'     => $this->sensor,
            'createdAt'  => $this->createdAt,
            'updatedAt'  => $this->updatedAt,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->id === '';
    }

    /** @return array<string, mixed> */
    public function toPersistence(): array
    {
        return array_filter([
            'sensor_id'   => $this->sensorId,
            'company_id'  => $this->companyId,
            'value'       => $this->value,
            'unit'        => $this->unit,
            'measured_at' => $this->measuredAt,
            'notes'       => $this->notes,
        ], static fn (float | string | null $v): bool => $v !== null);
    }
}
