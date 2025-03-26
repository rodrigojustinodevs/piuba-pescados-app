<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batche;

use App\Presentation\Resources\Batche\BatcheResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Domain\Repositories\BatcheRepositoryInterface;

class ListBatchesUseCase
{
    public function __construct(
        protected BatcheRepositoryInterface $batcheRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->batcheRepository->paginate();

        return BatcheResource::collection($response->items())
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
