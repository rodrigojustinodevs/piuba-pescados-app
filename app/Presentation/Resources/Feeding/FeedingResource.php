<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Feeding;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read \Illuminate\Support\Carbon|null $feeding_date
 * @property-read float $quantity_provided
 * @property-read string $feed_type
 * @property-read string|null $stock_id
 * @property-read float $stock_reduction_quantity
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Batch|null $batch
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
        $feedingDate = $this->feeding_date;

        if ($feedingDate instanceof \DateTimeInterface) {
            $feedingDate = $feedingDate->format('Y-m-d');
        }

        return [
            'id'    => $this->id,
            'batch' => $this->whenLoaded('batch', fn (): array => [
                'id'   => $this->batch->id,
                'name' => $this->batch->name,
            ]),
            'feedingDate'            => $feedingDate,
            'quantityProvided'       => $this->quantity_provided,
            'feedType'               => $this->feed_type,
            'stockId'                => $this->stock_id ?? null,
            'stockReductionQuantity' => $this->stock_reduction_quantity,
            'createdAt'              => $this->created_at?->toDateTimeString(),
            'updatedAt'              => $this->updated_at?->toDateTimeString(),
        ];
    }
}
