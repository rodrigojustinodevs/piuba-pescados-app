<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Domain\Repositories\TransferRepositoryInterface;
use App\Presentation\Resources\Transfer\TransferResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListTransfersUseCase
{
    public function __construct(
        protected TransferRepositoryInterface $transferRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->transferRepository->paginate();

        return TransferResource::collection($response->items())
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
