<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Domain\Repositories\MortalityRepositoryInterface;
use App\Presentation\Resources\Mortality\MortalityResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListMortalitiesUseCase
{
    public function __construct(
        protected MortalityRepositoryInterface $mortalityRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->mortalityRepository->paginate();

        return MortalityResource::collection($response->items())
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
