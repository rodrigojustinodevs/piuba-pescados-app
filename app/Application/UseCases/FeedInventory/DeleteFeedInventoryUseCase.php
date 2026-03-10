<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedInventory;

use App\Domain\Repositories\FeedInventoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteFeedInventoryUseCase
{
    public function __construct(
        protected FeedInventoryRepositoryInterface $feedInventoryRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->feedInventoryRepository->delete($id));
    }
}
