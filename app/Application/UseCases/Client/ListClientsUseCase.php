<?php

declare(strict_types=1);

namespace App\Application\UseCases\Client;

use App\Domain\Repositories\ClientRepositoryInterface;
use App\Presentation\Resources\Client\ClientResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListClientsUseCase
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->clientRepository->paginate();

        return ClientResource::collection($response->items())
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
