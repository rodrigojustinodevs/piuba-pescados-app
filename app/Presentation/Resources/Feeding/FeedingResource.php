<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Feeding;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string                          $id
 * @property-read string                          $batch_id
 * @property-read \Illuminate\Support\Carbon|null $feeding_date
 * @property-read float                           $quantity_provided
 * @property-read string                          $feed_type
 * @property-read string|null                     $stock_id
 * @property-read float                           $stock_reduction_quantity
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Batch|null   $batch
 */
final class FeedingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'batchId'                => $this->batch_id,
            'feedingDate'            => $this->feeding_date?->toDateString(),
            'quantityProvided'       => (float) $this->quantity_provided,
            'feedType'               => $this->feed_type,
            'stockId'                => $this->stock_id,
            'stockReductionQuantity' => (float) $this->stock_reduction_quantity,
            'createdAt'              => $this->created_at?->toDateTimeString(),
            'updatedAt'              => $this->updated_at?->toDateTimeString(),

            'batch' => $this->whenLoaded('batch', fn (): array => [
                'id'   => $this->batch->id,
                'name' => $this->batch->name,
            ]),
        ];
    }
}
