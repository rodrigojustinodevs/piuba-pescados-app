<?php

declare(strict_types=1);

namespace App\Application\UseCases\Harvest;

use App\Application\DTOs\HarvestDTO;
use App\Domain\Repositories\HarvestRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateHarvestUseCase
{
    public function __construct(
        protected HarvestRepositoryInterface $harvestRepository
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): HarvestDTO
    {
        return DB::transaction(function () use ($data): HarvestDTO {
            $harvest = $this->harvestRepository->create($data);

            $harvestDate = $harvest->harvest_date instanceof Carbon
                ? $harvest->harvest_date
                : Carbon::parse($harvest->harvest_date);

            return new HarvestDTO(
                id: $harvest->id,
                batcheId: $harvest->batche_id,
                harvestDate: $harvestDate->toDateString(),
                totalWeight: $harvest->total_weight,
                pricePerKg: $harvest->price_per_kg,
                totalRevenue: $harvest->total_revenue,
                createdAt: $harvest->created_at?->toDateTimeString(),
                updatedAt: $harvest->updated_at?->toDateTimeString()
            );
        });
    }
}
