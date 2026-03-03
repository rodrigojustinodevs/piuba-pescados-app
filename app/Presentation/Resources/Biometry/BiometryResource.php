<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Biometry;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read float $average_weight
 * @property-read float $fcr
 * @property-read \Illuminate\Support\Carbon|null $biometry_date
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Batch|null $batch
 */
class BiometryResource extends JsonResource
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
        $biometryDate = $this->biometry_date;
        if ($biometryDate instanceof \DateTimeInterface) {
            $biometryDate = $biometryDate->format('Y-m-d');
        }

        return [
            'id'            => $this->id,
            'batch'        => $this->whenLoaded('batch', fn (): array => [
                'id'   => $this->batch->id,
                'name' => $this->batch->name,
            ]),
            'biometryDate'  => $biometryDate,
            'averageWeight' => $this->average_weight,
            'fcr'           => $this->fcr,
            'createdAt'     => $this->created_at?->toDateTimeString(),
            'updatedAt'     => $this->updated_at?->toDateTimeString(),
        ];
    }
}
