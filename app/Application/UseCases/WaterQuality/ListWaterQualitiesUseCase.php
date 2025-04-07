<?php

declare(strict_types=1);

namespace App\Application\UseCases\WaterQuality;

use App\Domain\Repositories\WaterQualityRepositoryInterface;
use App\Presentation\Resources\WaterQuality\WaterQualityResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListWaterQualitiesUseCase
{
    public function __construct(
        protected WaterQualityRepositoryInterface $waterQualityRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->waterQualityRepository->paginate();

        return WaterQualityResource::collection($response->items())
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
