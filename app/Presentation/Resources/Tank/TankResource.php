<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Tank;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read int $capacity_liters
 * @property-read string $location
 * @property-read string $status
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\TankType|null $tankType
 * @property-read \App\Domain\Models\Company|null $company
 */
final class TankResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'capacityLiters' => $this->capacity_liters,
            'location'       => $this->location,
            'status'         => $this->status,
            'tankType'       => $this->whenLoaded('tankType', fn (): array => [
                'id'   => $this->tankType->id,
                'name' => $this->tankType->name,
            ]),
            'company' => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
