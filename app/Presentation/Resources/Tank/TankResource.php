<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Tank;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read int $capacity_liters
 * @property-read int $volume
 * @property-read string $location
 * @property-read string $status
 * @property-read string|null $cultivation
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\TankType|null $tankType
 * @property-read \App\Domain\Models\Company|null $company
 */
class TankResource extends JsonResource
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
            'id'              => $this->id,
            'name'            => $this->name,
            'capacity_liters' => $this->capacity_liters,
            'volume'          => $this->volume,
            'location'        => $this->location,
            'status'          => $this->status,
            'cultivation'     => $this->cultivation,
            'tank_type'       => $this->whenLoaded('tankType', fn (): array => [
                'id'   => $this->tankType->id,
                'name' => $this->tankType->name,
            ]),
            'company' => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
