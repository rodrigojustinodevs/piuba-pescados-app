<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Feeding;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedingRepository implements FeedingRepositoryInterface
{
    /**
     * Create a new feeding.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Feeding
    {
        return Feeding::create($data);
    }

    /**
     * Update an existing feeding.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Feeding
    {
        $feeding = Feeding::find($id);

        if ($feeding) {
            $feeding->update($data);

            return $feeding;
        }

        return null;
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<int, Feeding> $paginator */
        $paginator = Feeding::with([
            'batch:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show feeding by field and value.
     */
    public function showFeeding(string $field, string | int $value): ?Feeding
    {
        return Feeding::where($field, $value)->first();
    }

    /**
     * Get the average daily consumption for a company and feed type.
     */
    public function getDailyConsumptionAverage(string $companyId, string $feedType): float
    {
        // Faz o agrupamento e média direto no Banco de Dados
        $average = Feeding::query()
            ->whereHas('batch.tank', fn ($q) => $q->where('company_id', $companyId))
            ->where('feed_type', $feedType)
            ->selectRaw('DATE(feeding_date) as date, SUM(stock_reduction_quantity) as daily_total')
            ->groupBy('date')
            ->get()
            ->avg('daily_total');

        return (float) ($average ?? 0.0);
    }

    public function delete(string $id): bool
    {
        $feeding = Feeding::find($id);

        if (! $feeding) {
            return false;
        }

        return (bool) $feeding->delete();
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
        $sum = Feeding::query()
            ->where('batch_id', $batchId)
            ->whereBetween('feeding_date', [$startDate, $endDate])
            ->sum('stock_reduction_quantity');

        return (float) $sum;
    }

    public function getTotalFeedConsumedByBatch(string $batchId): float
    {
        return (float) Feeding::query()
            ->where('batch_id', $batchId)
            ->sum('quantity_provided');
    }
}
