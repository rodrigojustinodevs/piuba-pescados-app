<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Application\DTOs\FeedingDTO;
use App\Domain\Models\Feeding;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Infrastructure\Mappers\FeedingMapper;
use RuntimeException;

class ShowFeedingUseCase
{
    public function __construct(
        protected FeedingRepositoryInterface $feedingRepository
    ) {
    }

    public function execute(string $id): ?FeedingDTO
    {
        $feeding = $this->feedingRepository->showFeeding('id', $id);

        if (! $feeding instanceof Feeding) {
            throw new RuntimeException('Feeding not found');
        }

        return FeedingMapper::toDTO($feeding);
    }
}
