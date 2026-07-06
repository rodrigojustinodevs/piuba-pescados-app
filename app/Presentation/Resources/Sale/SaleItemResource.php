<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Sale;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Models\SaleItem
 */
final class SaleItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'batchId'         => $this->batch_id,
            'stockingId'      => $this->stocking_id,
            'productName'     => $this->product_name,
            'species'         => $this->species,
            'category'        => $this->category,
            'totalWeight'     => (float) $this->total_weight,
            'pricePerKg'      => (float) $this->price_per_kg,
            'subtotal'        => (float) $this->subtotal,
            'unitCost'        => (float) $this->unit_cost,
            'totalCost'       => (float) $this->total_cost,
            'isTotalHarvest'  => (bool) $this->is_total_harvest,
            'notes'           => $this->notes,

            'stocking' => $this->whenLoaded('stocking', fn (): array => [
                'id'            => $this->stocking->id,
                'quantity'      => $this->stocking->quantity,
                'averageWeight' => $this->stocking->average_weight,
            ]),
            'batch' => $this->whenLoaded('batch', fn (): array => [
                'id'   => $this->batch->id,
                'name' => $this->batch->name,
            ]),
        ];
    }
}
