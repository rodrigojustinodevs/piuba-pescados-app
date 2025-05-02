<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\HarvestDTO;
use App\Application\UseCases\Harvest\CreateHarvestUseCase;
use App\Application\UseCases\Harvest\DeleteHarvestUseCase;
use App\Application\UseCases\Harvest\ListHarvestsUseCase;
use App\Application\UseCases\Harvest\ShowHarvestUseCase;
use App\Application\UseCases\Harvest\UpdateHarvestUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HarvestService
{
    public function __construct(
        protected CreateHarvestUseCase $createHarvestUseCase,
        protected ListHarvestsUseCase $listHarvestsUseCase,
        protected ShowHarvestUseCase $showHarvestUseCase,
        protected UpdateHarvestUseCase $updateHarvestUseCase,
        protected DeleteHarvestUseCase $deleteHarvestUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): HarvestDTO
    {
        return $this->createHarvestUseCase->execute($data);
    }

    public function showAllHarvests(): AnonymousResourceCollection
    {
        return $this->listHarvestsUseCase->execute();
    }

    public function showHarvest(string $id): ?HarvestDTO
    {
        return $this->showHarvestUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateHarvest(string $id, array $data): HarvestDTO
    {
        return $this->updateHarvestUseCase->execute($id, $data);
    }

    public function deleteHarvest(string $id): bool
    {
        return $this->deleteHarvestUseCase->execute($id);
    }
}
