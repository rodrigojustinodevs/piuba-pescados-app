<?php

declare(strict_types=1);

namespace App\Application\UseCases\GrowthCurve;

use App\Application\DTOs\GrowthCurveDTO;
use App\Domain\Models\GrowthCurve;
use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use RuntimeException;

class ShowGrowthCurveUseCase
{
    public function __construct(
        protected GrowthCurveRepositoryInterface $growthCurveRepository
    ) {
    }

    public function execute(string $id): ?GrowthCurveDTO
    {
        $growthCurve = $this->growthCurveRepository->showGrowthCurve('id', $id);

        if (! $growthCurve instanceof GrowthCurve) {
            throw new RuntimeException('GrowthCurve not found');
        }

        return new GrowthCurveDTO(
            id: $growthCurve->id,
            batcheId: $growthCurve->batche_id,
            averageWeight: $growthCurve->average_weight,
            createdAt: $growthCurve->created_at?->toDateTimeString(),
            updatedAt: $growthCurve->updated_at?->toDateTimeString()
        );
    }
}
