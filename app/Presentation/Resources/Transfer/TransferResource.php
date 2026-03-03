<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Transfer;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $batch_id
 * @property-read string $origin_tank_id
 * @property-read string $destination_tank_id
 * @property-read int $quantity
 * @property-read string $description
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Batch|null $batch
 * @property-read \App\Domain\Models\Tank|null $originTank
 * @property-read \App\Domain\Models\Tank|null $destinationTank
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
            'batch'             => $this->whenLoaded('batch', fn (): array => [
                'id'   => $this->batch->id,
                'name' => $this->batch->name,
            ]),
            'originTank'        => $this->whenLoaded('originTank', fn (): array => [
                'id'   => $this->originTank->id,
                'name' => $this->originTank->name,
            ]),
            'destinationTank'   => $this->whenLoaded('destinationTank', fn (): array => [
                'id'   => $this->destinationTank->id,
                'name' => $this->destinationTank->name,
            ]),
            'quantity'          => $this->quantity,
            'description'       => $this->description,
            'createdAt'         => $this->created_at?->toDateTimeString(),
            'updatedAt'         => $this->updated_at?->toDateTimeString(),
        ];
    }
}
