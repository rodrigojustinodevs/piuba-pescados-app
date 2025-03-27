<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Presentation\Resources\Feeding\FeedingResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListFeedingsUseCase
{
    public function __construct(
        protected FeedingRepositoryInterface $feedingRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->feedingRepository->paginate();

        return FeedingResource::collection($response->items())
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
