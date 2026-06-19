<?php

declare(strict_types=1);

namespace App\Application\UseCases\Harvest;

use App\Application\DTOs\HarvestDTO;
use App\Domain\Repositories\HarvestRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateHarvestUseCase
{
    public function __construct(
        protected HarvestRepositoryInterface $harvestRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): HarvestDTO
    {
        $classifications = $data['size_classifications'] ?? [];
        unset($data['size_classifications']);
        $data = $this->computeAggregates($data, $classifications);

        return DB::transaction(function () use ($data, $classifications): HarvestDTO {
            $harvest = $this->harvestRepository->create($data);

            if ($classifications !== []) {
                $harvest->sizeClassifications()->createMany(
                    array_map(fn (array $item): array => [
                        'class'          => $item['class'],
                        'quantity'       => $item['quantity'],
                        'average_weight' => $item['average_weight'],
                        'price_per_kg'   => $item['price_per_kg'],
                    ], $classifications)
                );
            }

            $harvest->load('sizeClassifications');

            return HarvestDTOAssembler::fromModel($harvest);
        });
    }

    /**
     * Calcula total_weight, total_revenue e price_per_kg a partir das classificações.
     *
     * @param array<string, mixed>          $data
     * @param array<int, array<string, mixed>> $classifications
     * @return array<string, mixed>
     */
    private function computeAggregates(array $data, array $classifications): array
    {
        $totalWeight  = 0.0;
        $totalRevenue = 0.0;

        foreach ($classifications as $item) {
            $weightKg = ((float) $item['quantity'] * (float) $item['average_weight']) / 1000;
            $totalWeight += $weightKg;
            $totalRevenue += $weightKg * (float) $item['price_per_kg'];
        }

        $data['total_weight']  = round($totalWeight, 3);
        $data['total_revenue'] = round($totalRevenue, 2);
        $data['price_per_kg']  = $totalWeight > 0
            ? round($totalRevenue / $totalWeight, 2)
            : 0.0;

        return $data;
    }
}
