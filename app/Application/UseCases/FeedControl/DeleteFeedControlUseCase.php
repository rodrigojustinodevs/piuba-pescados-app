<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedControl;

use App\Domain\Repositories\FeedControlRepositoryInterface;

class DeleteFeedControlUseCase
{
    public function __construct(
        protected FeedControlRepositoryInterface $feedControlRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return $this->feedControlRepository->delete($id);
    }
}
