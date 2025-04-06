<?php

declare(strict_types=1);

namespace App\Presentation\Resources\SensorReading;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read float $value
 * @property-read \Illuminate\Support\Carbon|null $reading_date
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Sensor|null $sensor
 */
class SensorReadingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray($request): array
    {
        $readingDate = $this->reading_date instanceof Carbon
            ? $this->reading_date
            : Carbon::parse($this->reading_date);

        return [
            'id'          => $this->id,
            'value'       => $this->value,
            'readingDate' => $readingDate->toDateTimeString(), // Removido nullsafe
            'sensor'      => $this->whenLoaded('sensor', fn (): array => [
                'id'   => $this->sensor->id,
                'type' => $this->sensor->sensor_type,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
