<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Stock;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $supply_name
 * @property-read float $current_quantity
 * @property-read string $unit
 * @property-read float $minimum_stock
 * @property-read float $withdrawn_quantity
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Company|null $company
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
            'supplyName'        => $this->supply_name,
            'currentQuantity'   => $this->current_quantity,
            'unit'              => $this->unit,
            'minimumStock'      => $this->minimum_stock,
            'withdrawnQuantity' => $this->withdrawn_quantity,
            'company'           => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
