<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedInventory;

use App\Application\DTOs\FeedInventoryDTO;
use App\Domain\Models\FeedInventory;
use App\Domain\Repositories\FeedInventoryRepositoryInterface;
use Exception;

class ShowFeedInventoryUseCase
{
    public function __construct(
        protected FeedInventoryRepositoryInterface $feedInventoryRepository
    ) {
    }

    public function execute(string $id): ?FeedInventoryDTO
    {
        $feedInventory = $this->feedInventoryRepository->showFeedInventory('id', $id);

        if (! $feedInventory instanceof FeedInventory) {
            throw new Exception('FeedInventory not found');
        }

        return new FeedInventoryDTO(
            id: $feedInventory->id,
            feedType: $feedInventory->feed_type,
            currentStock: $feedInventory->current_stock,
            minimumStock: $feedInventory->minimum_stock,
            dailyConsumption: $feedInventory->daily_consumption,
            totalConsumption: $feedInventory->total_consumption,
            company: [
                'name' => $feedInventory->company->name ?? '',
            ],
            createdAt: $feedInventory->created_at?->toDateTimeString(),
            updatedAt: $feedInventory->updated_at?->toDateTimeString()
        );
    }
}
