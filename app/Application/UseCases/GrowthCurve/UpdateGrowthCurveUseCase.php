<?php

declare(strict_types=1);

namespace App\Application\UseCases\GrowthCurve;

use App\Application\DTOs\GrowthCurveDTO;
use App\Domain\Models\GrowthCurve;
use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateGrowthCurveUseCase
{
    public function __construct(
        protected GrowthCurveRepositoryInterface $growthCurveRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): GrowthCurveDTO
    {
        return DB::transaction(function () use ($id, $data): GrowthCurveDTO {
            $growthCurve = $this->growthCurveRepository->update($id, $data);

            if (! $growthCurve instanceof GrowthCurve) {
                throw new RuntimeException('Growth curve not found');
            }

            return new GrowthCurveDTO(
                id: $growthCurve->id,
                averageWeight: $growthCurve->average_weight,
                batcheId: $growthCurve->batche_id,
                createdAt: $growthCurve->created_at?->toDateTimeString(),
                updatedAt: $growthCurve->updated_at?->toDateTimeString()
            );
        });
    }
}
