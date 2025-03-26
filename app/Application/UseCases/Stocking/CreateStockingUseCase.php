<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Application\DTOs\StockingDTO;
use App\Domain\Repositories\StockingRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateStockingUseCase
{
    public function __construct(
        protected StockingRepositoryInterface $stockingRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): StockingDTO
    {
        return DB::transaction(function () use ($data): StockingDTO {
            $stocking = $this->stockingRepository->create($data);

            $stockingDate = $stocking->stocking_date instanceof Carbon
                ? $stocking->stocking_date
                : Carbon::parse($stocking->stocking_date);

            return new StockingDTO(
                id: $stocking->id,
                batcheId: $stocking->batche_id,
                stockingDate: $stockingDate->toDateString(),
                quantity: $stocking->quantity,
                averageWeight: $stocking->average_weight,
                createdAt: $stocking->created_at?->toDateTimeString(),
                updatedAt: $stocking->updated_at?->toDateTimeString()
            );
        });
    }
}
