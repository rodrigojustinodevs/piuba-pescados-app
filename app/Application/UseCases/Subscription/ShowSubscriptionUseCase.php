<?php

declare(strict_types=1);

namespace App\Application\UseCases\Subscription;

use App\Application\DTOs\SubscriptionDTO;
use App\Domain\Models\Subscription;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use Carbon\Carbon;
use RuntimeException;

class ShowSubscriptionUseCase
{
    public function __construct(protected SubscriptionRepositoryInterface $repository)
    {
    }

    public function execute(string $id): ?SubscriptionDTO
    {
        $subscription = $this->repository->findById($id);

        if (! $subscription instanceof Subscription) {
            throw new RuntimeException('Subscription not found');
        }

        $subscriptionStartDate = Carbon::parse($subscription->start_date);
        $subscriptionEndDate   = Carbon::parse($subscription->end_date);

        return new SubscriptionDTO(
            id: $subscription->id,
            plan: $subscription->plan,
            status: $subscription->status,
            startDate: $subscriptionStartDate->toDateString(),
            endDate: $subscriptionEndDate->toDateString(),
            company: [
                'name' => $subscription->company->name ?? '',
            ],
            createdAt: $subscription->created_at?->toDateTimeString(),
            updatedAt: $subscription->updated_at?->toDateTimeString()
        );
    }
}
