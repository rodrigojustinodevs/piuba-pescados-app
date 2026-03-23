<?php

declare(strict_types=1);

namespace App\Presentation\Resources\SensorReading;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Models\SensorReading
 */
final class SensorReadingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'sensorId'   => $this->sensor_id,
            'companyId'  => $this->company_id,
            'value'      => (float) $this->value,
            'unit'       => $this->unit,
            'measuredAt' => $this->measured_at?->toDateTimeString(),
            'notes'      => $this->notes,
            'createdAt'  => $this->created_at?->toDateTimeString(),
            'updatedAt'  => $this->updated_at?->toDateTimeString(),

            'sensor' => $this->whenLoaded('sensor', fn (): array => [
                'id'         => $this->sensor->id,
                'sensorType' => $this->sensor->sensor_type,
                'status'     => $this->sensor->status,
                'tank'       => $this->sensor->relationLoaded('tank') ? [
                    'id'   => $this->sensor->tank->id,
                    'name' => $this->sensor->tank->name,
                ] : null,
            ]),
        ];
    }
}
