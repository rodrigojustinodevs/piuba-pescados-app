<?php

declare(strict_types=1);

namespace App\Application\UseCases\GrowthCurve;

use App\Application\DTOs\GrowthCurveInputDTO;
use App\Domain\Models\GrowthCurve;
use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateGrowthCurveUseCase
{
    public function __construct(
        private GrowthCurveRepositoryInterface $growthCurveRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): GrowthCurve
    {
        $growthCurve = $this->growthCurveRepository->findOrFail($id);
        $dto         = GrowthCurveInputDTO::fromArray($data);

        return DB::transaction(function () use ($growthCurve, $dto): GrowthCurve {
            $growthCurve = $this->growthCurveRepository->update($growthCurve->id, [
                'batch_id'       => $dto->batchId,
                'average_weight' => $dto->averageWeight,
            ]);

            return $growthCurve->refresh();
        });
    }
}
