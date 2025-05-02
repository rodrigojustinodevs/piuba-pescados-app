<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Application\DTOs\FeedingDTO;
use App\Domain\Repositories\FeedingRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateFeedingUseCase
{
    public function __construct(
        protected FeedingRepositoryInterface $feedingRepository
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): FeedingDTO
    {
        return DB::transaction(function () use ($data): FeedingDTO {
            $feeding = $this->feedingRepository->create($data);

            $feedingDate = $feeding->feeding_date instanceof Carbon
                ? $feeding->feeding_date
                : Carbon::parse($feeding->feeding_date);

            return new FeedingDTO(
                id: $feeding->id,
                batcheId: $feeding->batche_id,
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
