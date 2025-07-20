<?php

declare(strict_types=1);

namespace App\Application\UseCases\Subscription;

use App\Application\DTOs\SubscriptionDTO;
use App\Domain\Models\Subscription;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateSubscriptionUseCase
{
    public function __construct(protected SubscriptionRepositoryInterface $repository)
    {
    }

    /**
     * @param array<string, mixed> $data
     * @throws RuntimeException
     */
    public function execute(string $id, array $data): SubscriptionDTO
    {
        return DB::transaction(function () use ($id, $data): \App\Application\DTOs\SubscriptionDTO {
            $subscription = $this->repository->update($id, $data);

            if (! $subscription instanceof Subscription) {
                throw new RuntimeException('Subscription not found');
            }

            return SubscriptionDTO::fromArray($subscription->toArray());
        });
    }
}
