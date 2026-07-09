<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Mortality;

use App\Domain\Enums\MortalitySeverity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string                         $id
 * @property-read string                         $batch_id
 * @property-read \Illuminate\Support\Carbon|null $mortality_date
 * @property-read int                            $quantity
 * @property-read \App\Domain\Enums\MortalityCause $cause
 * @property-read string|null                    $description
 * @property-read MortalitySeverity|null         $severity
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
            'id'              => $this->id,
            'mortalityDate'   => $this->mortality_date?->toDateString(),
            'quantity'        => $this->quantity,
            'cause'           => $this->cause->value,
            'description'     => $this->description,
            'severity'        => $this->severity,
            'batchPercentage' => $this->whenLoaded('batch', function (): float {
                $initial = (int) ($this->batch->initial_quantity ?? 0);

                return $initial > 0 ? round(($this->quantity / $initial) * 100, 2) : 0.0;
            }),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
            'batchId'   => $this->batch_id,
            'batch'     => $this->whenLoaded('batch', fn (): array => [
                'id'              => $this->batch->id,
                'name'            => $this->batch->name,
                'initialQuantity' => $this->batch->initial_quantity,
                'status'          => $this->batch->status,
            ]),
        ];
    }
}
