<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Application\DTOs\StockingDTO;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\StockingRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class UpdateStockingUseCase
{
    public function __construct(
        protected StockingRepositoryInterface $stockingRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): StockingDTO
    {
        return DB::transaction(function () use ($id, $data): StockingDTO {
            $stocking = $this->stockingRepository->update($id, $data);

            if (! $stocking instanceof Stocking) {
                throw new Exception('Stocking not found');
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
        });
    }
}
