<?php

declare(strict_types=1);

namespace App\Application\UseCases\Settlement;

use App\Domain\Repositories\SettlementRepositoryInterface;
use App\Presentation\Resources\Settlement\SettlementResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListSettlementsUseCase
{
    public function __construct(
        protected SettlementRepositoryInterface $settlementRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->settlementRepository->paginate();

        return SettlementResource::collection($response->items())
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
