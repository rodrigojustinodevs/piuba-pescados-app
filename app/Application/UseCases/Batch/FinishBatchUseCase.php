<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Application\Actions\Batch\CalculateBatchFinalReportAction;
use App\Domain\Enums\BatchStatus;
use App\Domain\Exceptions\BatchAlreadyFinishedException;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\HarvestRepositoryInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class FinishBatchUseCase
{
    public function __construct(
        private BatchRepositoryInterface $batchRepository,
        private HarvestRepositoryInterface $harvestRepository,
        private StockRepositoryInterface $stockRepository,
        private FeedingRepositoryInterface $feedingRepository,
        private CalculateBatchFinalReportAction $calculateReport,
    ) {
    }

    /**
     * @param array{
     *     total_weight: float|int,
     *     price_per_kg: float|int,
     *     harvest_date?: string
     * } $harvestData
     *
     * @return array<string, float>
     */
    public function execute(string $batchId, array $harvestData): array
    {
        $batch = $this->batchRepository->findOrFail($batchId);

        if ($batch->isFinished()) {
            throw new BatchAlreadyFinishedException((string) $batch->id);
        }

        return DB::transaction(function () use ($batch, $harvestData): array {
            $totalRevenue = $harvestData['total_weight'] * $harvestData['price_per_kg'];

            $harvest = $this->harvestRepository->create([
                'batch_id'      => $batch->id,
                'total_weight'  => $harvestData['total_weight'],
                'price_per_kg'  => $harvestData['price_per_kg'],
                'total_revenue' => $totalRevenue,
                'harvest_date'  => $harvestData['harvest_date'] ?? now()->toDateString(),
            ]);

            $this->batchRepository->update((string) $batch->id, [
                'status' => BatchStatus::FINISHED->value,
            ]);

            $latestFeeding = $this->feedingRepository->findLatestByBatch((string) $batch->id);
            $feedPrice     = 0.0;

            if ($latestFeeding?->stock_id !== null) {
                $feedPrice = $this->stockRepository->getUnitPriceByStockId($latestFeeding->stock_id);
            }

            return $this->calculateReport->execute(
                $batch,
                (float) $harvest->total_weight,
                (float) $harvest->price_per_kg,
                $feedPrice,
            );
        });
    }
}
