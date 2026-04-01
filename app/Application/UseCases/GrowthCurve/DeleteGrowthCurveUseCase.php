<?php

declare(strict_types=1);

namespace App\Application\UseCases\GrowthCurve;

use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteGrowthCurveUseCase
{
    public function __construct(
        private GrowthCurveRepositoryInterface $growthCurveRepository,
    ) {
    }

    public function execute(string $id): void
    {
        $this->growthCurveRepository->findOrFail($id);

        DB::transaction(function () use ($id): void {
            $this->growthCurveRepository->delete($id);
        });
    }
}
