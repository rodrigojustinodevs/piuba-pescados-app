<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Domain\Repositories\FeedingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteFeedingUseCase
{
    public function __construct(
        protected FeedingRepositoryInterface $feedingRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->feedingRepository->delete($id));
    }
}
