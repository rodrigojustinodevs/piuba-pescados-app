<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Sale;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $batche_id
 * @property-read float $total_weight
 * @property-read float $price_per_kg
 * @property-read float $total_revenue
 * @property-read string $sale_date
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Company|null $company
 * @property-read \App\Domain\Models\Client|null $client
 */
class SaleResource extends JsonResource
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
            'id'           => $this->id,
            'totalWeight'  => $this->total_weight,
            'pricePerKg'   => $this->price_per_kg,
            'totalRevenue' => $this->total_revenue,
            'saleDate'     => $this->sale_date,
            'company'      => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name ?? '',
            ]),
            'client' => $this->whenLoaded('client', fn (): array => [
                'id'   => $this->client->id ?? '',
                'name' => $this->client->name ?? '',
            ]),
            'batcheId'  => $this->batche_id,
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
