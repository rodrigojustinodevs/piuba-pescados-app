<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string                          $id
 * @property string                          $stock_id
 * @property string                          $supply_id
 * @property float                           $quantity
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Domain\Models\Supply|null $supply
 * @property-read \App\Domain\Models\Stock|null  $stock
 */
final class StockBalanceResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'stockId'   => $this->stock_id,
            'supplyId'  => $this->supply_id,
            'quantity'  => (float) $this->quantity,
            'updatedAt' => $this->updated_at?->toDateTimeString(),

            'supply' => $this->whenLoaded('supply', fn (): array => [
                'id'   => $this->supply->id,
                'name' => $this->supply->name,
                'unit' => $this->supply->unit,
            ]),

            'stock' => $this->whenLoaded('stock', fn (): array => [
                'id'   => $this->stock->id,
                'name' => $this->stock->name,
                'code' => $this->stock->code,
            ]),
        ];
    }
}
