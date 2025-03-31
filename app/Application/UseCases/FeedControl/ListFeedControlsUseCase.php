<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedControl;

use App\Domain\Repositories\FeedControlRepositoryInterface;
use App\Presentation\Resources\FeedControl\FeedControlResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListFeedControlsUseCase
{
    public function __construct(
        protected FeedControlRepositoryInterface $feedControlRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->feedControlRepository->paginate();

        return FeedControlResource::collection($response->items())
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
