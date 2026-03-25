<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Stocking;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StockingRepository implements StockingRepositoryInterface
{
    /**
     * Create a new stocking.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Stocking
    {
        return Stocking::create($data);
    }

    /**
     * Update an existing stocking.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Stocking
    {
        $stocking = Stocking::find($id);

        if ($stocking) {
            $stocking->update($data);

            return $stocking;
        }

        return null;
    }

    /**
     * Get paginated stockings.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<int, Stocking> $paginator */
        $paginator = Stocking::with([
            'batch:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show stocking by field and value.
     */
    public function showStocking(string $field, string | int $value): ?Stocking
    {
        return Stocking::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $stocking = Stocking::find($id);

        if (! $stocking) {
            return false;
        }

        return (bool) $stocking->delete();
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
     * Generated SQL (example with 2 rows):
     *   UPDATE stockings
     *   SET accumulated_fixed_cost = GREATEST(0, CASE id
     *       WHEN ? THEN accumulated_fixed_cost + ?
     *       WHEN ? THEN accumulated_fixed_cost + ?
     *   END)
     *   WHERE id IN (?, ?)
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

        // GREATEST(0, ...) prevents the column from going negative on decrement
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
            ->firstOrFail(); // lança ModelNotFoundException com mensagem padrão do Laravel
    }
}
