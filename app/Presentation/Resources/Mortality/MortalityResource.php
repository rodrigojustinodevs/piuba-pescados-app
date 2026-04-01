<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Mortality;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string                         $id
 * @property-read string                         $batch_id
 * @property-read \Illuminate\Support\Carbon|null $mortality_date
 * @property-read int                            $quantity
 * @property-read string                         $cause
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Batch|null  $batch
 */
final class MortalityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'mortalityDate' => $this->mortality_date?->toDateString(),
            'quantity'      => $this->quantity,
            'cause'         => $this->cause,
            'createdAt'     => $this->created_at?->toDateTimeString(),
            'updatedAt'     => $this->updated_at?->toDateTimeString(),

            'batch' => $this->whenLoaded('batch', fn (): array => [
                'id'              => $this->batch->id,
                'name'            => $this->batch->name,
                'initialQuantity' => $this->batch->initial_quantity,
                'status'          => $this->batch->status,
            ]),
        ];
    }
}
