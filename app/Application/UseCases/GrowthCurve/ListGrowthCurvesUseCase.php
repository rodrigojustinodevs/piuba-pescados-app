<?php

declare(strict_types=1);

namespace App\Application\UseCases\GrowthCurve;

use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use App\Presentation\Resources\GrowthCurve\GrowthCurveResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListGrowthCurvesUseCase
{
    public function __construct(
        protected GrowthCurveRepositoryInterface $purchaseRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->purchaseRepository->paginate();

        return GrowthCurveResource::collection($response->items())
            ->additional([
                'pagination' => [
                    'total'        => $response->total(),
                    'current_page' => $response->currentPage(),
                    'last_page'    => $response->lastPage(),
                    'first_page'   => $response->firstPage(),
                    'per_page'     => $response->perPage(),
                ],
            ]);
    }
}
