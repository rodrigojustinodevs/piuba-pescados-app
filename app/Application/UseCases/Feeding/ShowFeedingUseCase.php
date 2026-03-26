<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Domain\Models\Feeding;
use App\Domain\Repositories\FeedingRepositoryInterface;
use RuntimeException;

class ShowFeedingUseCase
{
    public function __construct(
        protected FeedingRepositoryInterface $feedingRepository
    ) {
    }

    public function execute(string $id): Feeding
    {
        $feeding = $this->feedingRepository->showFeeding('id', $id);

        if (! $feeding instanceof Feeding) {
            throw new RuntimeException('Feeding not found');
        }

        return $feeding;
    }
}
