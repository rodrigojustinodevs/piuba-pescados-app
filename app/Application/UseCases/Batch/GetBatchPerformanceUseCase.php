<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batch;

use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use App\Domain\Services\Batch\BatchPerformanceService;
use App\Domain\Models\Biometry;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class GetBatchPerformanceUseCase
{
    /** Cache por 10 minutos; dados biológicos não mudam a cada segundo. */
    private const int CACHE_TTL_SECONDS = 600;

    private const string CACHE_KEY_PREFIX = 'batch_performance:';

    public function __construct(
        private BatchRepositoryInterface $batchRepository,
        private BatchPerformanceService $performanceService,
        private BiometryRepositoryInterface $biometryRepository,
        private FeedingRepositoryInterface $feedingRepository,
        private StockRepositoryInterface $stockRepository
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
            fn () => $this->getPerformance($batchId)
        );
    }

    /**
     * Invalida o cache de performance do lote (ex.: após nova biometria/mortalidade/feeding).
     */
    public function invalidateCache(string $batchId): void
    {
        Cache::forget(self::CACHE_KEY_PREFIX . $batchId);
    }

    /**
     * @return array<string, mixed>
     */
    private function getPerformance(string $batchId): array
    {
        $batch = $this->batchRepository->showBatch('id', $batchId);

        if (! $batch instanceof Batch) {
            throw new RuntimeException('Batch not found');
        }

        $latestBiometry = $this->biometryRepository
            ->findLatestByBatch($batchId);

        $latestFeeding = $this->feedingRepository->findLatestByBatch($batchId);
        $feedType = $latestFeeding?->feed_type ?? '';

        $feedPrice = $this->stockRepository->getUnitPrice(
            $batch->tank->company_id,
            $feedType
        );

        return $this->performanceService->buildPerformance(
            $batch,
            $latestBiometry,
            $feedPrice
        );
    }
}
