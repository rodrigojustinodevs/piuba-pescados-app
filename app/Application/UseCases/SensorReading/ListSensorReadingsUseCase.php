<?php

declare(strict_types=1);

namespace App\Application\UseCases\SensorReading;

use App\Domain\Repositories\SensorReadingRepositoryInterface;
use App\Presentation\Resources\SensorReading\SensorReadingResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListSensorReadingsUseCase
{
    public function __construct(
        protected SensorReadingRepositoryInterface $sensorReadingRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->sensorReadingRepository->paginate();

        return SensorReadingResource::collection($response->items())
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
