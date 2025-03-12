<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Batche;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $entry_date
 * @property-read int $initial_quantity
 * @property-read int $species
 * @property-read string $status
 * @property-read string|null $cultivation
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Tank|null $tank
 */
class BatcheResource extends JsonResource
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
            'entryDate'       => $this->entry_date,
            'initialQuantity' => $this->initial_quantity,
            'species'         => $this->species,
            'status'          => $this->status,
            'cultivation'     => $this->cultivation,
            'tank'            => $this->whenLoaded('tankType', fn (): array => [
                'id'   => $this->tank->id,
                'name' => $this->tank->name,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
