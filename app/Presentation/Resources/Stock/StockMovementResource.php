<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Stock;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string                               $id
 * @property string                               $stock_id
 * @property string                               $supply_id
 * @property string                               $user_id
 * @property \App\Domain\Enums\StockMovementTypeEnum $type
 * @property float                                $quantity
 * @property string|null                          $reason
 * @property \Illuminate\Support\Carbon|null      $created_at
 *
 * @property-read \App\Domain\Models\Supply|null $supply
 * @property-read \App\Domain\Models\Stock|null  $stock
 */
final class StockMovementResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'stockId'   => $this->stock_id,
            'supplyId'  => $this->supply_id,
            'userId'    => $this->user_id,
            'type'      => $this->type->value,
            'typeLabel' => $this->type->label(),
            'quantity'  => (float) $this->quantity,
            'reason'    => $this->reason,
            'createdAt' => $this->created_at?->toDateTimeString(),

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
