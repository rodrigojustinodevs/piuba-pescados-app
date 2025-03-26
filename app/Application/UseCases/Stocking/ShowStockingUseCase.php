<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Application\DTOs\StockingDTO;
use App\Domain\Repositories\StockingRepositoryInterface;
use Carbon\Carbon;

class ShowStockingUseCase
{
    public function __construct(
        protected StockingRepositoryInterface $stockingRepository
    ) {
    }

    public function execute(string $id): ?StockingDTO
    {
        $stocking = $this->stockingRepository->showStocking('id', $id);

        if (!$stocking instanceof \App\Domain\Models\Stocking) {
            return null;
        }

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
    }
}
