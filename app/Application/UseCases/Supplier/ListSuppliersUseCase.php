<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supplier;

use App\Domain\Repositories\SupplierRepositoryInterface;
use App\Presentation\Resources\Supplier\SupplierResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListSuppliersUseCase
{
    public function __construct(
        protected SupplierRepositoryInterface $supplierRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->supplierRepository->paginate();

        return SupplierResource::collection($response->items())
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
