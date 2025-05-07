<?php

declare(strict_types=1);

namespace App\Application\UseCases\Harvest;

use App\Domain\Repositories\HarvestRepositoryInterface;
use App\Presentation\Resources\Harvest\HarvestResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListHarvestsUseCase
{
    public function __construct(
        protected HarvestRepositoryInterface $harvestRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->harvestRepository->paginate();

        return HarvestResource::collection($response->items())
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
