<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Application\Services\Batch\BatchPerformanceService;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use Illuminate\Support\Facades\Cache;

final readonly class GetBatchPerformanceUseCase
{
    private const int CACHE_TTL_SECONDS = 600;

    private const string CACHE_KEY_PREFIX = 'batch_performance:';

    public function __construct(
        private BatchRepositoryInterface $batchRepository,
        private BatchPerformanceService $performanceService,
        private BiometryRepositoryInterface $biometryRepository,
        private FeedingRepositoryInterface $feedingRepository,
        private StockRepositoryInterface $stockRepository,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(string $batchId): array
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $batchId;

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->getPerformance($batchId),
        );
    }

    public function invalidateCache(string $batchId): void
    {
        Cache::forget(self::CACHE_KEY_PREFIX . $batchId);
    }

    /**
     * @return array<string, mixed>
     */
    private function getPerformance(string $batchId): array
    {
        $batch = $this->batchRepository->findOrFail($batchId);

        $latestBiometry = $this->biometryRepository->findLatestByBatch($batchId);

        $latestFeeding = $this->feedingRepository->findLatestByBatch($batchId);
        $feedPrice     = 0.0;

        if ($latestFeeding?->stock_id !== null) {
            $feedPrice = $this->stockRepository->getUnitPriceByStockId($latestFeeding->stock_id);
        }

        return $this->performanceService->buildPerformance($batch, $latestBiometry, $feedPrice);
    }
}
