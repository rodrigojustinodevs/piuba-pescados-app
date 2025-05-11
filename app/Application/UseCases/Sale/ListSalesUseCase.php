<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Domain\Repositories\SaleRepositoryInterface;
use App\Presentation\Resources\Sale\SaleResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListSalesUseCase
{
    public function __construct(
        protected SaleRepositoryInterface $purchaseRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->purchaseRepository->paginate();

        return SaleResource::collection($response->items())
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
