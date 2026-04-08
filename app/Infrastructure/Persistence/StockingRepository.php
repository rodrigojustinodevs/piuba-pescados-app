<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\StockingInputDTO;
use App\Domain\Enums\StockingStatus;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class StockingRepository implements StockingRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'batch:id,name,tank_id',
    ];

    /**
     * @param array{
     *     batchId?: string|null,
     *     companyId?: string|null,
     *     dateFrom?: string|null,
     *     dateTo?: string|null,
     *     status?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $paginator = Stocking::with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['batchId']),
                static fn ($q) => $q->where('batch_id', $filters['batchId']),
            )
            ->when(
                ! empty($filters['companyId']),
                static function ($q) use ($filters): void {
                    $q->whereHas(
                        'batch.tank',
                        static fn ($tq) => $tq->where('company_id', $filters['companyId']),
                    );
                },
            )
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where('status', $filters['status']),
            )
            ->when(
                ! empty($filters['dateFrom']),
                static fn ($q) => $q->whereDate('stocking_date', '>=', $filters['dateFrom']),
            )
            ->when(
                ! empty($filters['dateTo']),
                static fn ($q) => $q->whereDate('stocking_date', '<=', $filters['dateTo']),
            )
            ->latest('stocking_date')
            ->paginate((int) ($filters['perPage'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): Stocking
    {
        return Stocking::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    public function showStocking(string $field, string | int $value): ?Stocking
    {
        return Stocking::with(self::DEFAULT_RELATIONS)->where($field, $value)->first();
    }

    public function create(StockingInputDTO $dto): Stocking
    {
        /** @var Stocking $stocking */
        $stocking = Stocking::create($dto->toPersistence());

        return $stocking->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Stocking
    {
        $stocking = $this->findOrFail($id);
        $stocking->update($attributes);

        return $stocking->refresh();
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function bulkIncrementFixedCost(array $amountsByStockingId): void
    {
        if ($amountsByStockingId === []) {
            return;
        }

        $this->bulkAdjustFixedCost($amountsByStockingId, '+');
    }

    public function bulkDecrementFixedCost(array $amountsByStockingId): void
    {
        if ($amountsByStockingId === []) {
            return;
        }

        $this->bulkAdjustFixedCost($amountsByStockingId, '-');
    }

    /**
     * Builds and executes a single CASE WHEN UPDATE for all stockings.
     *
     * @param array<string, float> $amountsByStockingId
     * @param '+'|'-'              $operator
     */
    private function bulkAdjustFixedCost(array $amountsByStockingId, string $operator): void
    {
        $caseSql      = 'CASE id';
        $caseBindings = [];

        foreach ($amountsByStockingId as $stockingId => $amount) {
            $caseSql .= ' WHEN ? THEN accumulated_fixed_cost ' . $operator . ' ?';
            $caseBindings[] = $stockingId;
            $caseBindings[] = abs($amount);
        }

        $caseSql .= ' END';

        $ids      = array_keys($amountsByStockingId);
        $inSql    = implode(', ', array_fill(0, count($ids), '?'));
        $bindings = array_merge($caseBindings, $ids);

        DB::statement(
            "UPDATE stockings SET accumulated_fixed_cost = GREATEST(0, {$caseSql}) WHERE id IN ({$inSql})",
            $bindings,
        );
    }

    public function findByCompanyOrFail(string $stockingId, string $companyId): Stocking
    {
        /** @var Stocking */
        return Stocking::where('id', $stockingId)
            ->whereHas(
                'batch.tank',
                static fn ($q) => $q->where('company_id', $companyId),
            )
            ->firstOrFail();
    }

    public function findByBatchId(string $batchId): ?Stocking
    {
        return Stocking::with(self::DEFAULT_RELATIONS)
            ->where('batch_id', $batchId)
            ->where('status', StockingStatus::ACTIVE)
            ->latest('stocking_date')
            ->first();
    }

    public function hasActiveStockingsInBatch(string $batchId, string $excludeStockingId = null): bool
    {
        return Stocking::with(self::DEFAULT_RELATIONS)
            ->where('batch_id', $batchId)
            ->where('status', StockingStatus::ACTIVE)
            ->when($excludeStockingId, static function ($query, string $id): void {
                $query->where('id', '!=', $id);
            })
            ->exists();
    }

    public function totalAccumulatedCost(string $stockingId): float
    {
        return (float) Stocking::with(self::DEFAULT_RELATIONS)
            ->where('id', $stockingId)
            ->sum('accumulated_fixed_cost');
    }

    public function findOrFailLocked(string $id): Stocking
    {
        return Stocking::with(self::DEFAULT_RELATIONS)
            ->where('id', $id)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
