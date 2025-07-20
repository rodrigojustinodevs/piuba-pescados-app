<?php

declare(strict_types=1);

namespace App\Application\UseCases\Subscription;

use App\Domain\Repositories\SubscriptionRepositoryInterface;
use App\Presentation\Resources\Subscription\SubscriptionResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListSubscriptionsUseCase
{
    public function __construct(protected SubscriptionRepositoryInterface $repository)
    {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->repository->paginate();

        return SubscriptionResource::collection($response->items())
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
