<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $id
 * @property string|null $stock_id
 * @property string $company_id
 * @property string|null $supply_id
 * @property string|null $supplier_id
 * @property string|null $reference_id
 * @property string|null $reference_type
 * @property float $quantity
 * @property string $unit
 * @property float $unit_price
 * @property float $total_cost
 * @property string      $direction
 * @property string|null $location
 * @property string|null $responsible
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Domain\Models\Supply|null $supply
 */
final class StockTransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'direction'     => $this->direction,
            'quantity'      => (float) $this->quantity,
            'unit'          => $this->unit,
            'unitPrice'     => (float) $this->unit_price,
            'totalCost'     => (float) $this->total_cost,
            'referenceType' => $this->reference_type,
            'referenceId'   => $this->reference_id,
            'supply'        => $this->whenLoaded('supply', fn (): array => [
                'id'   => $this->supply->id,
                'name' => $this->supply->name,
            ]),
            'location'    => $this->location,
            'responsible' => $this->responsible,
            'notes'       => $this->notes,
            'createdAt'   => $this->created_at?->toDateTimeString(),
        ];
    }
}
