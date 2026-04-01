<?php

declare(strict_types=1);

namespace App\Application\UseCases\GrowthCurve;

use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final readonly class ListGrowthCurvesUseCase
{
    public function __construct(
        private GrowthCurveRepositoryInterface $growthCurveRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        return $this->growthCurveRepository->paginate($filters);
    }
}
