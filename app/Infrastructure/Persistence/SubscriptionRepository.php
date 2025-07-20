<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Subscription;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SubscriptionRepositoryInterface;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Subscription
    {
        return Subscription::create($data);
    }

    public function findById(string $id): ?Subscription
    {
        return Subscription::find($id);
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var \Illuminate\Pagination\LengthAwarePaginator<Subscription> $paginator */
        $paginator = Subscription::with([
            'company:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Subscription
    {
        $subscription = $this->findById($id);

        if (!$subscription instanceof \App\Domain\Models\Subscription) {
            return null;
        }

        $subscription->update($data);

        return $subscription;
    }

    public function delete(string $id): bool
    {
        $subscription = $this->findById($id);

        if (!$subscription instanceof \App\Domain\Models\Subscription) {
            return false;
        }

        return (bool) $subscription->delete();
    }
}
