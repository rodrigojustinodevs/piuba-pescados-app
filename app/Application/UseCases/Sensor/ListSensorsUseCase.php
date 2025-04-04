<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sensor;

use App\Domain\Repositories\SensorRepositoryInterface;
use App\Presentation\Resources\Sensor\SensorResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListSensorsUseCase
{
    public function __construct(
        protected SensorRepositoryInterface $sensorRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->sensorRepository->paginate();

        return SensorResource::collection($response->items())
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
