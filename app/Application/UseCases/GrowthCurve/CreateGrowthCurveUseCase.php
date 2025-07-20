<?php

declare(strict_types=1);

namespace App\Application\UseCases\GrowthCurve;

use App\Application\DTOs\GrowthCurveDTO;
use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateGrowthCurveUseCase
{
    public function __construct(
        protected GrowthCurveRepositoryInterface $growthCurveRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): GrowthCurveDTO
    {
        return DB::transaction(function () use ($data): GrowthCurveDTO {
            $growthCurve = $this->growthCurveRepository->create($data);

            return new GrowthCurveDTO(
                id: $growthCurve->id,
                batcheId: $growthCurve->batche_id,
                averageWeight: $growthCurve->average_weight,
                createdAt: $growthCurve->created_at?->toDateTimeString(),
                updatedAt: $growthCurve->updated_at?->toDateTimeString()
            );
        });
    }
}
