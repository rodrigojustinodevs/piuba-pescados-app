<?php

declare(strict_types=1);

namespace App\Application\UseCases\Alert;

use App\Domain\Repositories\AlertRepositoryInterface;
use App\Presentation\Resources\Alert\AlertResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListAlertsUseCase
{
    public function __construct(protected AlertRepositoryInterface $repository)
    {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->repository->paginate();

        return AlertResource::collection($response->items())
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
