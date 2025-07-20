<?php

declare(strict_types=1);

namespace App\Application\UseCases\Subscription;

use App\Domain\Repositories\SubscriptionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteSubscriptionUseCase
{
    public function __construct(protected SubscriptionRepositoryInterface $repository)
    {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->repository->delete($id));
    }
}
