<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Domain\Repositories\PurchaseRepositoryInterface;
use App\Presentation\Resources\Purchase\PurchaseResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListPurchasesUseCase
{
    public function __construct(
        protected PurchaseRepositoryInterface $purchaseRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->purchaseRepository->paginate();

        return PurchaseResource::collection($response->items())
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
