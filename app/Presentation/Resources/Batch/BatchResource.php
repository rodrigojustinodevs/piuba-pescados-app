<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Batch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string                          $id
 * @property-read string|null                     $name
 * @property-read string|null                     $description
 * @property-read \Illuminate\Support\Carbon|null $entry_date
 * @property-read int                             $initial_quantity
 * @property-read string                          $species
 * @property-read string                          $status
 * @property-read string|null                     $cultivation
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Tank|null    $tank
 */
final class BatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'description'     => $this->description,
            'entryDate'       => $this->entry_date?->toDateString(),
            'initialQuantity' => $this->initial_quantity,
            'species'         => $this->species,
            'status'          => $this->status,
            'cultivation'     => $this->cultivation,
            'createdAt'       => $this->created_at?->toDateTimeString(),
            'updatedAt'       => $this->updated_at?->toDateTimeString(),

            'tank' => $this->whenLoaded('tank', fn (): array => [
                'id'   => $this->tank->id,
                'name' => $this->tank->name,
            ]),
        ];
    }
}
