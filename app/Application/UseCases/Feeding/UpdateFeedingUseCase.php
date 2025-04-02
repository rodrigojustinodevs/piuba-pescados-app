<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Application\DTOs\FeedingDTO;
use App\Domain\Models\Feeding;
use App\Domain\Repositories\FeedingRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateFeedingUseCase
{
    public function __construct(
        protected FeedingRepositoryInterface $feedingRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): FeedingDTO
    {
        return DB::transaction(function () use ($id, $data): FeedingDTO {
            $feeding = $this->feedingRepository->update($id, $data);

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
        });
    }
}
