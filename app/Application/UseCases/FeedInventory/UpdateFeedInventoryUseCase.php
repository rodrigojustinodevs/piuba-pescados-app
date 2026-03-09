<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedInventory;

use App\Application\DTOs\FeedInventoryDTO;
use App\Domain\Models\FeedInventory;
use App\Domain\Repositories\FeedInventoryRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateFeedInventoryUseCase
{
    public function __construct(
        protected FeedInventoryRepositoryInterface $feedInventoryRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @throws RuntimeException
     */
    public function execute(string $id, array $data): FeedInventoryDTO
    {
        return DB::transaction(function () use ($id, $data): FeedInventoryDTO {
            $feedInventory = $this->feedInventoryRepository->update($id, $data);

            if (! $feedInventory instanceof FeedInventory) {
                throw new RuntimeException('FeedInventory not found');
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
        });
    }
}
