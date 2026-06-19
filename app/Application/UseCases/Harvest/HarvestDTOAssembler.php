<?php

declare(strict_types=1);

namespace App\Application\UseCases\Harvest;

use App\Application\DTOs\HarvestDTO;
use App\Domain\Models\Harvest;
use Carbon\Carbon;

final class HarvestDTOAssembler
{
    public static function fromModel(Harvest $harvest): HarvestDTO
    {
        $harvestDate = $harvest->harvest_date instanceof Carbon
            ? $harvest->harvest_date
            : Carbon::parse($harvest->harvest_date);

        $classifications = $harvest->relationLoaded('sizeClassifications')
            ? $harvest->sizeClassifications->map(fn ($c): array => [
                'id'            => $c->id,
                'class'         => $c->class,
                'quantity'      => $c->quantity,
                'averageWeight' => $c->average_weight,
                'pricePerKg'    => $c->price_per_kg,
                'totalWeight'   => $c->totalWeight(),
                'revenue'       => $c->revenue(),
            ])->values()->all()
            : [];

        return new HarvestDTO(
            id:                  $harvest->id,
            batchId:             $harvest->batch_id,
            tankId:              $harvest->tank_id,
            harvestDate:         $harvestDate->toDateString(),
            type:                $harvest->type->value,
            status:              $harvest->status->value,
            destination:         $harvest->destination?->value,
            initialPopulation:   $harvest->initial_population,
            harvestedQuantity:   $harvest->harvested_quantity,
            averageWeight:       $harvest->average_weight,
            totalWeight:         $harvest->total_weight,
            pricePerKg:          $harvest->price_per_kg,
            totalRevenue:        $harvest->total_revenue,
            operationalCost:     $harvest->operational_cost,
            netProfit:           $harvest->netProfit(),
            survivalRate:        $harvest->survivalRate(),
            clientDestination:   $harvest->client_destination,
            responsible:         $harvest->responsible,
            notes:               $harvest->notes,
            sizeClassifications: $classifications,
            createdAt:           $harvest->created_at?->toDateTimeString(),
            updatedAt:           $harvest->updated_at?->toDateTimeString(),
        );
    }
}
