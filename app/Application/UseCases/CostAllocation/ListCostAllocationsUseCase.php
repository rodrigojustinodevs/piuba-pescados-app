<?php

declare(strict_types=1);

namespace App\Application\UseCases\CostAllocation;

use App\Domain\Repositories\CostAllocationRepositoryInterface;
use App\Presentation\Resources\CostAllocation\CostAllocationResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListCostAllocationsUseCase
{
    public function __construct(
        protected CostAllocationRepositoryInterface $costAllocationRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->costAllocationRepository->paginate();

        return CostAllocationResource::collection($response->items())
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
