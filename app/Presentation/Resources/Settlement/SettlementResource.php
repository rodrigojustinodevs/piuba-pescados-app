<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Settlement;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $settlement_date
 * @property-read int $quantity
 * @property-read int $average_weight
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Batche|null $batche
 */
class SettlementResource extends JsonResource
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
            'id'             => $this->id,
            'batcheId'       => $this->batche?->id,
            'settlementDate' => $this->settlement_date,
            'quantity'       => $this->quantity,
            'averageWeight'  => $this->average_weight,
            'createdAt'      => $this->created_at?->toDateTimeString(),
            'updatedAt'      => $this->updated_at?->toDateTimeString(),
        ];
    }
}
