<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Feeding;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $feeding_date
 * @property-read float $quantity_provided
 * @property-read string $feed_type
 * @property-read float $stock_reduction_quantity
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Batche|null $batche
 */
class FeedingResource extends JsonResource
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
            'id'                     => $this->id,
            'batcheId'               => $this->batche?->id,
            'feedingDate'            => $this->feeding_date,
            'quantityProvided'       => $this->quantity_provided,
            'feedType'               => $this->feed_type,
            'stockReductionQuantity' => $this->stock_reduction_quantity,
            'createdAt'              => $this->created_at?->toDateTimeString(),
            'updatedAt'              => $this->updated_at?->toDateTimeString(),
        ];
    }
}
