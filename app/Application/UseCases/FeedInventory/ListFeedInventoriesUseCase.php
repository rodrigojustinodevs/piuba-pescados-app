<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedInventory;

use App\Domain\Repositories\FeedInventoryRepositoryInterface;
use App\Presentation\Resources\FeedInventory\FeedInventoryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListFeedInventoriesUseCase
{
    public function __construct(
        protected FeedInventoryRepositoryInterface $feedInventoryRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->feedInventoryRepository->paginate();

        return FeedInventoryResource::collection($response->items())
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
