<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\HarvestRepositoryInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Services\Batch\BatchClosingService;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Domain\Models\Batch;
use RuntimeException;

class FinishBatchUseCase
{
    public function __construct(
        private BatchRepositoryInterface $batchRepository,
        private HarvestRepositoryInterface $harvestRepository,
        private StockRepositoryInterface $stockRepository,
        private BatchClosingService $closingService,
        private FeedingRepositoryInterface $feedingRepository
    ) {}

    public function execute(string $batchId, array $harvestData): array
    {
        return DB::transaction(function () use ($batchId, $harvestData) {
            $batch = $this->batchRepository->showBatch('id', $batchId);

            if (! $batch instanceof Batch) {
                throw new RuntimeException('Batch not found');
            }

            if ($batch->status === 'finished') {
                throw new Exception("Este lote já foi finalizado.");
            }

            $totalRevenue = $harvestData['total_weight'] * $harvestData['price_per_kg'];

            $harvest = $this->harvestRepository->create([
                'batch_id' => $batch->id,
                'total_weight' => $harvestData['total_weight'],
                'price_per_kg' => $harvestData['price_per_kg'],
                'total_revenue' => $totalRevenue,
                'harvest_date' => $harvestData['harvest_date'] ?? now()->toDateString(),
            ]);

            $this->batchRepository->update($batch->id, [
                'status' => 'finished'
            ]);

            $latestFeeding = $this->feedingRepository->findLatestByBatch($batchId);
            $feedType = $latestFeeding?->feed_type ?? '';

            $feedPrice = $this->stockRepository->getUnitPrice($batch->tank->company_id, $feedType);

            return $this->closingService->calculateFinalReport(
                $batch, 
                (float) $harvest->total_weight, 
                (float) $harvest->price_per_kg, 
                (float) $feedPrice
            );
        });
    }
}