<?php

declare(strict_types=1);

namespace App\Application\UseCases\Tank;

use App\Domain\Repositories\TankRepositoryInterface;
use App\Presentation\Resources\Tank\TankResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShowAllTanksUseCase
{
    public function __construct(
        protected TankRepositoryInterface $tankRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->tankRepository->paginate();

        return TankResource::collection($response->items())
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
