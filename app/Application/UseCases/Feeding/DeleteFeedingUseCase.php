<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Domain\Repositories\FeedingRepositoryInterface;

class DeleteFeedingUseCase
{
    public function __construct(
        protected FeedingRepositoryInterface $feedingRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return $this->feedingRepository->delete($id);
    }
}
