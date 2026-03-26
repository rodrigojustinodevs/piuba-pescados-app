<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Domain\Models\Feeding;
use App\Domain\Repositories\FeedingRepositoryInterface;

final readonly class ShowFeedingUseCase
{
    public function __construct(
        private FeedingRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): Feeding
    {
        return $this->repository->findOrFail($id);
    }
}
