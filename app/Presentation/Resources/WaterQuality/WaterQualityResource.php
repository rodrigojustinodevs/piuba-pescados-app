<?php

declare(strict_types=1);

namespace App\Presentation\Resources\WaterQuality;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $tank_id
 * @property-read string $measured_at
 * @property-read float $ph
 * @property-read float $dissolved_oxygen
 * @property-read float $temperature
 * @property-read float $ammonia
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Tank|null $tank
 */
class WaterQualityResource extends JsonResource
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
        return [
            'id'           => $this->id,
            'measuredAt' => $this->measured_at,
            'ph'           => $this->ph,
            'dissolvedOxygen'       => $this->dissolved_oxygen,
            'temperature'  => $this->temperature,
            'ammonia'      => $this->ammonia,
            'tank'         => $this->whenLoaded('tank', fn (): array => [
                'id'   => $this->tank->id,
                'name' => $this->tank->name,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
