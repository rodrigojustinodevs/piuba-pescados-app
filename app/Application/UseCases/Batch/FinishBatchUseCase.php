<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\HarvestRepositoryInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use App\Domain\Services\Batch\BatchClosingService;
use Exception;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinishBatchUseCase
{
    public function __construct(
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly HarvestRepositoryInterface $harvestRepository,
        private readonly StockRepositoryInterface $stockRepository,
        private readonly BatchClosingService $closingService,
        private readonly FeedingRepositoryInterface $feedingRepository
    ) {
    }

    /**
     * @param array{
     *     total_weight: float|int,
     *     price_per_kg: float|int,
     *     harvest_date?: string
     * } $harvestData
     *
     * @return array<string, float|int>
     */
    public function execute(string $batchId, array $harvestData): array
    {
        return DB::transaction(function () use ($batchId, $harvestData): array {
            $batch = $this->batchRepository->showBatch('id', $batchId);

            if (! $batch instanceof Batch) {
                throw new RuntimeException('Batch not found');
            }

            if ($batch->status === 'finished') {
                throw new Exception("Este lote já foi finalizado.");
            }

            $totalRevenue = $harvestData['total_weight'] * $harvestData['price_per_kg'];

            $harvest = $this->harvestRepository->create([
                'batch_id'      => $batch->id,
                'total_weight'  => $harvestData['total_weight'],
                'price_per_kg'  => $harvestData['price_per_kg'],
                'total_revenue' => $totalRevenue,
                'harvest_date'  => $harvestData['harvest_date'] ?? now()->toDateString(),
            ]);

            $this->batchRepository->update($batch->id, [
                'status' => 'finished',
            ]);

            $latestFeeding = $this->feedingRepository->findLatestByBatch($batchId);
            $feedPrice     = 0.0;
            if ($latestFeeding?->stock_id !== null) {
                $feedPrice = $this->stockRepository->getUnitPriceByStockId($latestFeeding->stock_id);
            }

            return $this->closingService->calculateFinalReport(
                $batch,
                (float) $harvest->total_weight,
                (float) $harvest->price_per_kg,
                $feedPrice
            );
        });
    }
}
