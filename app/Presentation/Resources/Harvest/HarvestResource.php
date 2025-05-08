<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Harvest;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $batche_id
 * @property-read float $total_weight
 * @property-read float $price_per_kg
 * @property-read float $total_revenue
 * @property-read string $harvest_date
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 */
class HarvestResource extends JsonResource
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
            'batcheId'     => $this->batche_id,
            'totalWeight'  => $this->total_weight,
            'pricePerKg'   => $this->price_per_kg,
            'totalRevenue' => $this->total_revenue,
            'harvestDate'  => $this->harvest_date,
            'createdAt'    => $this->created_at?->toDateTimeString(),
            'updatedAt'    => $this->updated_at?->toDateTimeString(),
        ];
    }
}
