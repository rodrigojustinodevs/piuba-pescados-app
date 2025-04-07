<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\WaterQualityDTO;
use App\Application\UseCases\WaterQuality\CreateWaterQualityUseCase;
use App\Application\UseCases\WaterQuality\DeleteWaterQualityUseCase;
use App\Application\UseCases\WaterQuality\ListWaterQualitiesUseCase;
use App\Application\UseCases\WaterQuality\ShowWaterQualityUseCase;
use App\Application\UseCases\WaterQuality\UpdateWaterQualityUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WaterQualityService
{
    public function __construct(
        protected CreateWaterQualityUseCase $createWaterQualityUseCase,
        protected ListWaterQualitiesUseCase $listWaterQualitiesUseCase,
        protected ShowWaterQualityUseCase $showWaterQualityUseCase,
        protected UpdateWaterQualityUseCase $updateWaterQualityUseCase,
        protected DeleteWaterQualityUseCase $deleteWaterQualityUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): WaterQualityDTO
    {
        return $this->createWaterQualityUseCase->execute($data);
    }

    public function showAllWaterQualities(): AnonymousResourceCollection
    {
        return $this->listWaterQualitiesUseCase->execute();
    }

    public function showWaterQuality(string $id): ?WaterQualityDTO
    {
        return $this->showWaterQualityUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateWaterQuality(string $id, array $data): WaterQualityDTO
    {
        return $this->updateWaterQualityUseCase->execute($id, $data);
    }

    public function deleteWaterQuality(string $id): bool
    {
        return $this->deleteWaterQualityUseCase->execute($id);
    }
}
