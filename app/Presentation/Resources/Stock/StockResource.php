<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Stock;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read float $current_quantity
 * @property-read string $unit
 * @property-read float $unit_price
 * @property-read float $minimum_stock
 * @property-read float $withdrawal_quantity
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Company|null $company
 * @property-read \App\Domain\Models\Supplier|null $supplier
 */
class StockResource extends JsonResource
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
            'id'                => $this->id,
            'currentQuantity'   => $this->current_quantity,
            'unit'              => $this->unit,
            'unitPrice'         => $this->unit_price,
            'minimumStock'      => $this->minimum_stock,
            'withdrawnQuantity' => $this->withdrawal_quantity,
            'company'           => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'supplier' => $this->whenLoaded('supplier', fn (): array => [
                'id'   => $this->supplier->id,
                'name' => $this->supplier->name,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
