<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Domain\Repositories\StockRepositoryInterface;
use App\Presentation\Resources\Stock\StockResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListStocksUseCase
{
    public function __construct(
        protected StockRepositoryInterface $stockRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->stockRepository->paginate();

        return StockResource::collection($response->items())
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
