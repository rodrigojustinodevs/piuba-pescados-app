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
 * @property string $direction
 * @property \Illuminate\Support\Carbon|null $created_at
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
            'createdAt'     => $this->created_at?->toDateTimeString(),
        ];
    }
}
