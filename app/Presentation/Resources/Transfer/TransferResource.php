<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Transfer;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $batche_id
 * @property-read string $origin_tank_id
 * @property-read string $destination_tank_id
 * @property-read int $quantity
 * @property-read string $description
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 */
class TransferResource extends JsonResource
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
            'id'                => $this->id,
            'batcheId'          => $this->batche_id,
            'originTankId'      => $this->origin_tank_id,
            'destinationTankId' => $this->destination_tank_id,
            'quantity'          => $this->quantity,
            'description'       => $this->description,
            'created_at'        => $this->created_at?->toDateTimeString(),
            'updated_at'        => $this->updated_at?->toDateTimeString(),
        ];
    }
}
