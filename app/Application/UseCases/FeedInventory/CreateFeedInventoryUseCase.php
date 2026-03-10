<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedInventory;

use App\Application\DTOs\FeedInventoryDTO;
use App\Domain\Repositories\FeedInventoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateFeedInventoryUseCase
{
    public function __construct(
        protected FeedInventoryRepositoryInterface $feedInventoryRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): FeedInventoryDTO
    {
        return DB::transaction(function () use ($data): FeedInventoryDTO {
            $feedInventory = $this->feedInventoryRepository->create($data);

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
        });
    }
}
