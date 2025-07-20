<?php

declare(strict_types=1);

namespace App\Application\UseCases\Subscription;

use App\Domain\Repositories\SubscriptionRepositoryInterface;

class DeleteSubscriptionUseCase
{
    public function __construct(protected SubscriptionRepositoryInterface $repository)
    {
    }

    public function execute(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
