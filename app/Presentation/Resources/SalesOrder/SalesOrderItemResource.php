<?php

declare(strict_types=1);

namespace App\Presentation\Resources\SalesOrder;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Models\SalesOrderItem
 */
final class SalesOrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'quantity'    => (float) $this->quantity,
            'unitPrice'   => (float) $this->unit_price,
            'subtotal'    => (float) $this->subtotal,
            'measureUnit' => $this->measure_unit,
            'stocking'    => $this->whenLoaded('stocking', fn (): array => [
                'id'                   => $this->stocking->id,
                'quantity'             => $this->stocking->quantity,
                'averageWeight'        => $this->stocking->average_weight,
                'estimatedBiomass'     => $this->stocking->estimated_biomass,
                'accumulatedFixedCost' => $this->stocking->accumulated_fixed_cost,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
