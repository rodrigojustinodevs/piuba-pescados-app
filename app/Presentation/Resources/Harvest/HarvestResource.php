<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Harvest;

use App\Domain\Models\HarvestSizeClassification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string      $id
 * @property-read string      $batch_id
 * @property-read string|null $tank_id
 * @property-read string      $harvest_date
 * @property-read string      $type
 * @property-read string      $status
 * @property-read string|null $destination
 * @property-read int         $initial_population
 * @property-read int         $harvested_quantity
 * @property-read float       $average_weight
 * @property-read float       $total_weight
 * @property-read float       $price_per_kg
 * @property-read float       $total_revenue
 * @property-read float       $operational_cost
 * @property-read string|null $client_destination
 * @property-read string|null $responsible
 * @property-read string|null $notes
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Batch|null $batch
 * @property-read \App\Domain\Models\Tank|null $tank
 * @property-read Collection<int, HarvestSizeClassification> $sizeClassifications
 * @method float netProfit()
 * @method float survivalRate()
 */
class HarvestResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'batch' => $this->whenLoaded('batch', fn (): array => [
                'id'   => $this->batch->id,
                'name' => $this->batch->name,
            ]),
            'tank' => $this->whenLoaded('tank', fn (): array => [
                'id'   => $this->tank->id,
                'name' => $this->tank->name,
            ]),
            'harvestDate'         => $this->harvest_date,
            'type'                => $this->type,
            'status'              => $this->status,
            'destination'         => $this->destination,
            'initialPopulation'   => $this->initial_population,
            'harvestedQuantity'   => $this->harvested_quantity,
            'averageWeight'       => $this->average_weight,
            'totalWeight'         => $this->total_weight,
            'pricePerKg'          => $this->price_per_kg,
            'totalRevenue'        => $this->total_revenue,
            'operationalCost'     => $this->operational_cost,
            'netProfit'           => $this->netProfit(),
            'survivalRate'        => $this->survivalRate(),
            'clientDestination'   => $this->client_destination,
            'responsible'         => $this->responsible,
            'notes'               => $this->notes,
            'sizeClassifications' => $this->whenLoaded(
                'sizeClassifications',
                fn () => $this->sizeClassifications->map(fn ($c): array => [
                    'id'            => $c->id,
                    'class'         => $c->class,
                    'quantity'      => $c->quantity,
                    'averageWeight' => $c->average_weight,
                    'pricePerKg'    => $c->price_per_kg,
                    'totalWeight'   => $c->totalWeight(),
                    'revenue'       => $c->revenue(),
                ])->values(),
            ),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
