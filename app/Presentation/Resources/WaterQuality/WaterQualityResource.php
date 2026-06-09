<?php

declare(strict_types=1);

namespace App\Presentation\Resources\WaterQuality;

use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Models\WaterQuality
 */
final class WaterQualityResource extends JsonResource
{
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'tankId'          => $this->tank_id,
            'measuredAt'      => $this->formatDateTime($this->measured_at),
            'ph'              => $this->ph !== null ? (float) $this->ph : null,
            'dissolvedOxygen' => $this->dissolved_oxygen !== null ? (float) $this->dissolved_oxygen : null,
            'temperature'     => $this->temperature !== null ? (float) $this->temperature : null,
            'ammonia'         => $this->ammonia !== null ? (float) $this->ammonia : null,
            'salinity'        => $this->salinity !== null ? (float) $this->salinity : null,
            'turbidity'       => $this->turbidity !== null ? (float) $this->turbidity : null,
            'quality'         => $this->quality,
            'notes'           => $this->notes,

            'tank' => $this->whenLoaded('tank', fn (): array => [
                'id'       => $this->tank->id,
                'name'     => $this->tank->name,
                'tankType' => $this->tank->relationLoaded('tankType') && $this->tank->tankType !== null
                    ? $this->tank->tankType->name
                    : null,
                'sensor'   => $this->tank->relationLoaded('sensor') && $this->tank->sensor !== null
                    ? [
                        'id'          => $this->tank->sensor->id,
                        'sensorType'  => $this->tank->sensor->sensor_type,
                        'status'      => $this->tank->sensor->status,
                        'lastReading' => $this->tank->sensor->last_reading,
                        'unit'        => $this->tank->sensor->unit,
                    ]
                    : null,
            ]),

            'company' => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),

            'createdAt' => $this->formatDateTime($this->created_at),
            'updatedAt' => $this->formatDateTime($this->updated_at),
        ];
    }

    private function formatDateTime(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_string($value)) {
            return $value;
        }

        return (string) $value;
    }
}
