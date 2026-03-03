<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Stocking;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read \Illuminate\Support\Carbon|null $stocking_date
 * @property-read int $quantity
 * @property-read float $average_weight
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Batch|null $batch
 */
class StockingResource extends JsonResource
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
        $stockingDate = $this->stocking_date;

        if ($stockingDate instanceof \DateTimeInterface) {
            $stockingDate = $stockingDate->format('Y-m-d');
        }

        return [
            'id'    => $this->id,
            'batch' => $this->whenLoaded('batch', fn (): array => [
                'id'   => $this->batch->id,
                'name' => $this->batch->name,
            ]),
            'stockingDate'  => $stockingDate,
            'quantity'      => $this->quantity,
            'averageWeight' => $this->average_weight,
            'createdAt'     => $this->created_at?->toDateTimeString(),
            'updatedAt'     => $this->updated_at?->toDateTimeString(),
        ];
    }
}
