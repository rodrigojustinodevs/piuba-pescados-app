<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $id
 * @property string $company_id
 * @property string $supply_id
 * @property string $supplier_id
 * @property float $current_quantity
 * @property string $unit
 * @property float $unit_price
 * @property float $minimum_stock
 * @property float $withdrawal_quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Domain\Models\Supply|null $supply
 * @property-read \App\Domain\Models\Supplier|null $supplier
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Domain\Models\StockTransaction[] $transactions
 */
final class StockResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'companyId'          => $this->company_id,
            'supplyId'           => $this->supply_id,
            'supplierId'         => $this->supplier_id,
            'currentQuantity'    => (float) $this->current_quantity,
            'unit'               => $this->unit,
            'unitPrice'          => (float) $this->unit_price,
            'minimumStock'       => (float) $this->minimum_stock,
            'withdrawalQuantity' => (float) $this->withdrawal_quantity,
            'isBelowMinimum'     => $this->resource->isBelowMinimum(),
            'createdAt'          => $this->created_at?->toDateTimeString(),
            'updatedAt'          => $this->updated_at?->toDateTimeString(),

            'supply' => $this->whenLoaded('supply', fn (): array => [
                'id'          => $this->supply->id,
                'name'        => $this->supply->name,
                'defaultUnit' => $this->supply->default_unit,
            ]),

            'supplier' => $this->whenLoaded('supplier', fn (): array => [
                'id'   => $this->supplier->id,
                'name' => $this->supplier->name,
            ]),

            'transactions' => $this->whenLoaded(
                'transactions',
                fn (): array => $this->transactions->map(static fn ($t): array => [
                    'id'            => $t->id,
                    'direction'     => $t->direction,
                    'quantity'      => (float) $t->quantity,
                    'unitPrice'     => (float) $t->unit_price,
                    'totalCost'     => (float) $t->total_cost,
                    'referenceType' => $t->reference_type,
                    'referenceId'   => $t->reference_id,
                    'createdAt'     => $t->created_at?->toDateTimeString(),
                ])->all(),
            ),
        ];
    }
}
