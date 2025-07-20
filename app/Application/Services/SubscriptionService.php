<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\SubscriptionDTO;
use App\Application\UseCases\Subscription\CreateSubscriptionUseCase;
use App\Application\UseCases\Subscription\DeleteSubscriptionUseCase;
use App\Application\UseCases\Subscription\ListSubscriptionsUseCase;
use App\Application\UseCases\Subscription\ShowSubscriptionUseCase;
use App\Application\UseCases\Subscription\UpdateSubscriptionUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionService
{
    public function __construct(
        protected CreateSubscriptionUseCase $createSubscriptionUseCase,
        protected ListSubscriptionsUseCase $listSubscriptionsUseCase,
        protected ShowSubscriptionUseCase $showSubscriptionUseCase,
        protected UpdateSubscriptionUseCase $updateSubscriptionUseCase,
        protected DeleteSubscriptionUseCase $deleteSubscriptionUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): SubscriptionDTO
    {
        return $this->createSubscriptionUseCase->execute($data);
    }

    public function showAllSubscriptions(): AnonymousResourceCollection
    {
        return $this->listSubscriptionsUseCase->execute();
    }

    public function showSubscription(string $id): ?SubscriptionDTO
    {
        return $this->showSubscriptionUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateSubscription(string $id, array $data): SubscriptionDTO
    {
        return $this->updateSubscriptionUseCase->execute($id, $data);
    }

    public function deleteSubscription(string $id): bool
    {
        return $this->deleteSubscriptionUseCase->execute($id);
    }
}
