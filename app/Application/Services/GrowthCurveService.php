<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\GrowthCurveDTO;
use App\Application\UseCases\GrowthCurve\CreateGrowthCurveUseCase;
use App\Application\UseCases\GrowthCurve\DeleteGrowthCurveUseCase;
use App\Application\UseCases\GrowthCurve\ListGrowthCurvesUseCase;
use App\Application\UseCases\GrowthCurve\ShowGrowthCurveUseCase;
use App\Application\UseCases\GrowthCurve\UpdateGrowthCurveUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GrowthCurveService
{
    public function __construct(
        protected CreateGrowthCurveUseCase $createGrowthCurveUseCase,
        protected ListGrowthCurvesUseCase $listGrowthCurvesUseCase,
        protected ShowGrowthCurveUseCase $showGrowthCurveUseCase,
        protected UpdateGrowthCurveUseCase $updateGrowthCurveUseCase,
        protected DeleteGrowthCurveUseCase $deleteGrowthCurveUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): GrowthCurveDTO
    {
        return $this->createGrowthCurveUseCase->execute($data);
    }

    public function showAll(): AnonymousResourceCollection
    {
        return $this->listGrowthCurvesUseCase->execute();
    }

    public function show(string $id): ?GrowthCurveDTO
    {
        return $this->showGrowthCurveUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): GrowthCurveDTO
    {
        return $this->updateGrowthCurveUseCase->execute($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->deleteGrowthCurveUseCase->execute($id);
    }
}
