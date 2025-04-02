<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedControl;

use App\Application\DTOs\FeedControlDTO;
use App\Domain\Models\FeedControl;
use App\Domain\Repositories\FeedControlRepositoryInterface;
use Exception;

class ShowFeedControlUseCase
{
    public function __construct(
        protected FeedControlRepositoryInterface $feedControlRepository
    ) {
    }

    public function execute(string $id): ?FeedControlDTO
    {
        $feedControl = $this->feedControlRepository->showFeedControl('id', $id);

        if (! $feedControl instanceof FeedControl) {
            throw new Exception('FeedControl not found');
        }

        return new FeedControlDTO(
            id: $feedControl->id,
            feedType: $feedControl->feed_type,
            currentStock: $feedControl->current_stock,
            minimumStock: $feedControl->minimum_stock,
            dailyConsumption: $feedControl->daily_consumption,
            totalConsumption: $feedControl->total_consumption,
            company: [
                'name' => $feedControl->company->name ?? '',
            ],
            createdAt: $feedControl->created_at?->toDateTimeString(),
            updatedAt: $feedControl->updated_at?->toDateTimeString()
        );
    }
}
