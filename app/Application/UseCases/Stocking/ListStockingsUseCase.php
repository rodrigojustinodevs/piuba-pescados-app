<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Domain\Repositories\StockingRepositoryInterface;
use App\Presentation\Resources\Stocking\StockingResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListStockingsUseCase
{
    public function __construct(
        protected StockingRepositoryInterface $stockingRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->stockingRepository->paginate();

        return StockingResource::collection($response->items())
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
