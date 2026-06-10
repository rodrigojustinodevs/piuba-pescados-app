<?php

declare(strict_types=1);

namespace App\Application\UseCases\Harvest;

use App\Application\DTOs\HarvestDTO;
use App\Domain\Models\Harvest;
use App\Domain\Repositories\HarvestRepositoryInterface;
use RuntimeException;

class ShowHarvestUseCase
{
    public function __construct(
        protected HarvestRepositoryInterface $harvestRepository,
    ) {
    }

    public function execute(string $id): HarvestDTO
    {
        $harvest = $this->harvestRepository->showHarvest('id', $id);

        if (! $harvest instanceof Harvest) {
            throw new RuntimeException('Harvest not found');
        }

        $harvest->load('sizeClassifications');

        return HarvestDTOAssembler::fromModel($harvest);
    }
}
