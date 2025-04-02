<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Application\DTOs\FeedingDTO;
use App\Domain\Models\Feeding;
use App\Domain\Repositories\FeedingRepositoryInterface;
use Carbon\Carbon;
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

        $feedingDate = $feeding->feeding_date instanceof Carbon
        ? $feeding->feeding_date
        : Carbon::parse($feeding->feeding_date);

        return new FeedingDTO(
            id: $feeding->id,
            batcheID: $feeding->batche_id,
            feedingDate: $feedingDate->toDateString(),
            quantityProvided: $feeding->quantity_provided,
            feedType: $feeding->feed_type,
            stockReductionQuantity: $feeding->stock_reduction_quantity,
            createdAt: $feeding->created_at?->toDateTimeString(),
            updatedAt: $feeding->updated_at?->toDateTimeString()
        );
    }
}
