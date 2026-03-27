<?php

declare(strict_types=1);

namespace App\Application\UseCases\GrowthCurve;

use App\Domain\Models\GrowthCurve;
use App\Domain\Repositories\GrowthCurveRepositoryInterface;

final readonly class ShowGrowthCurveUseCase
{
    public function __construct(
        private GrowthCurveRepositoryInterface $growthCurveRepository,
    ) {
    }

    public function execute(string $id): GrowthCurve
    {
        return $this->growthCurveRepository->findOrFail($id);
    }
}
