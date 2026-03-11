<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Mortality;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $batch_id
 * @property-read \Illuminate\Support\Carbon|null $mortality_date
 * @property int $quantity
 * @property string $cause
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 */
class MortalityResource extends JsonResource
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
        $mortalityDate          = $this->mortality_date;
        $mortalityDateFormatted = $mortalityDate instanceof \DateTimeInterface
            ? $mortalityDate->format('Y-m-d')
            : '';

        return [
            'id'            => $this->id,
            'batchId'       => $this->batch_id,
            'mortalityDate' => $mortalityDateFormatted,
            'quantity'      => $this->quantity,
            'cause'         => $this->cause,
            'createdAt'     => $this->created_at?->toDateTimeString(),
            'updatedAt'     => $this->updated_at?->toDateTimeString(),
        ];
    }
}
