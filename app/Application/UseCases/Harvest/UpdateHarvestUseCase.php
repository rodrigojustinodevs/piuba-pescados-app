<?php

declare(strict_types=1);

namespace App\Application\UseCases\Harvest;

use App\Application\DTOs\HarvestDTO;
use App\Domain\Models\Harvest;
use App\Domain\Repositories\HarvestRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateHarvestUseCase
{
    public function __construct(
        protected HarvestRepositoryInterface $harvestRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): HarvestDTO
    {
        return DB::transaction(function () use ($id, $data): HarvestDTO {
            $classifications = $data['size_classifications'] ?? null;
            unset($data['size_classifications']);

            $harvest = $this->harvestRepository->update($id, $data);

            if (! $harvest instanceof Harvest) {
                throw new RuntimeException('Harvest not found');
            }

            if ($classifications !== null) {
                $harvest->sizeClassifications()->delete();

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
}
