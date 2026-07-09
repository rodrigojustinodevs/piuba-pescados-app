<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\FeedingInputDTO;
use App\Domain\Models\Feeding;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final class FeedingRepository implements FeedingRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'batch:id,name,tank_id,status',
        'stock:id,supply_id',
        'stock.supply:id,name',
    ];

    /**
     * @param array{
     *     search?: string|null,
     *     companyId?: string|null,
     *     batchId?: string|null,
     *     feedType?: string|null,
     *     dateFrom?: string|null,
     *     dateTo?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $search = $filters['search'] ?? null;

        $paginator = Feeding::with(self::DEFAULT_RELATIONS)
            ->when(
                is_string($search) && $search !== '',
                static function ($q) use ($search): void {
                    $pattern = '%' . $search . '%';
                    $q->where(static function ($query) use ($pattern): void {
                        $query->whereHas(
                            'batch',
                            static fn ($bq) => $bq->where('name', 'like', $pattern),
                        )->orWhereHas(
                            'stock.supply',
                            static fn ($sq) => $sq->where('name', 'like', $pattern),
                        );
                    });
                },
            )
            ->when(
                ! empty($filters['companyId']),
                static fn ($q) => $q->whereHas(
                    'batch.tank',
                    static fn ($tq) => $tq->where('company_id', $filters['companyId'])
                ),
            )
            ->when(
                ! empty($filters['batchId']),
                static fn ($q) => $q->where('batch_id', $filters['batchId']),
            )
            ->when(
                ! empty($filters['feedType']),
                static fn ($q) => $q->where('feed_type', $filters['feedType']),
            )
            ->when(
                ! empty($filters['dateFrom']),
                static fn ($q) => $q->whereDate('feeding_date', '>=', $filters['dateFrom']),
            )
            ->when(
                ! empty($filters['dateTo']),
                static fn ($q) => $q->whereDate('feeding_date', '<=', $filters['dateTo']),
            )
            ->latest('feeding_date')
            ->paginate((int) ($filters['perPage'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): Feeding
    {
        return Feeding::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    public function create(FeedingInputDTO $dto): Feeding
    {
        /** @var Feeding $feeding */
        $feeding = Feeding::create($dto->toPersistence());

        return $feeding->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Feeding
    {
        $feeding = $this->findOrFail($id);
        $feeding->update($attributes);

        return $feeding->refresh();
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function getDailyConsumptionAverage(string $companyId, string $feedType): float
    {
        $average = Feeding::query()
            ->whereHas('batch.tank', static fn ($q) => $q->where('company_id', $companyId))
            ->where('feed_type', $feedType)
            ->selectRaw('DATE(feeding_date) as date, SUM(stock_reduction_quantity) as daily_total')
            ->groupBy('date')
            ->get()
            ->avg('daily_total');

        return (float) ($average ?? 0.0);
    }

    public function findLatestByBatch(string $batchId): ?Feeding
    {
        return Feeding::query()
            ->where('batch_id', $batchId)
            ->orderByDesc('feeding_date')
            ->first();
    }

    public function existsByBatch(string $batchId): bool
    {
        return Feeding::where('batch_id', $batchId)->exists();
    }

    public function totalFeedConsumedUntilDate(string $batchId, string $startDate, string $endDate): float
    {
        return (float) Feeding::query()
            ->where('batch_id', $batchId)
            ->whereBetween('feeding_date', [$startDate, $endDate])
            ->sum('stock_reduction_quantity');
    }

    public function getTotalFeedConsumedByBatch(string $batchId): float
    {
        return (float) Feeding::query()
            ->where('batch_id', $batchId)
            ->sum('quantity_provided');
    }
}
