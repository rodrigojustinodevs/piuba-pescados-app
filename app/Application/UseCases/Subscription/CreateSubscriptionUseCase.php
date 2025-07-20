<?php

declare(strict_types=1);

namespace App\Application\UseCases\Subscription;

use App\Application\DTOs\SubscriptionDTO;
use App\Domain\Repositories\SubscriptionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateSubscriptionUseCase
{
    public function __construct(protected SubscriptionRepositoryInterface $repository)
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): SubscriptionDTO
    {
        return DB::transaction(function () use ($data): SubscriptionDTO {
            $subscription = $this->repository->create($data);
            return SubscriptionDTO::fromArray($subscription->toArray());
        });
    }
}
