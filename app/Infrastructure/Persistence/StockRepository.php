<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Stock;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

class StockRepository implements StockRepositoryInterface
{
    /**
     * Create a new stock.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Stock
    {
        return Stock::create($data);
    }

    /**
     * Update an existing stock.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Stock
    {
        $stock = Stock::find($id);

        if ($stock) {
            $stock->update($data);
            $stock->load(['company:id,name', 'supplier:id,name']);

            return $stock;
        }

        return null;
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<int, Stock> $paginator */
        $paginator = Stock::with([
            'company:id,name',
            'supplier:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show stock by field and value.
     */
    public function showStock(string $field, string | int $value): ?Stock
    {
        return Stock::with(['company:id,name', 'supplier:id,name'])
            ->where($field, $value)
            ->first();
    }

    /**
     * Find first stock by company and supplier.
     */
    public function findByCompanyAndSupplier(string $companyId, string $supplierId): ?Stock
    {
        return Stock::where('company_id', $companyId)
            ->where('supplier_id', $supplierId)
            ->first();
    }

    /**
     * Get unit price for a stock by ID.
     */
    public function getUnitPriceByStockId(string $stockId): float
    {
        $unitPrice = Stock::where('id', $stockId)->value('unit_price');

        if (! $unitPrice || ! is_numeric($unitPrice)) {
            throw new RuntimeException('Unit price not found');
        }

        return (float) $unitPrice;
    }

    public function decrementStock(string $id, float $quantity): bool
    {
        $stock = Stock::find($id);

        if (! $stock) {
            return false;
        }

        $stock->decrement('current_quantity', $quantity);
        $stock->increment('withdrawal_quantity', $quantity);

        return $stock->save();
    }

    public function incrementStock(string $id, float $quantity): bool
    {
        $stock = Stock::find($id);

        if (! $stock) {
            return false;
        }

        $stock->increment('current_quantity', $quantity);
        $stock->decrement('withdrawal_quantity', $quantity);

        return $stock->save();
    }

    public function delete(string $id): bool
    {
        $stock = Stock::find($id);

        if (! $stock) {
            return false;
        }

        return (bool) $stock->delete();
    }

    /**
     * Find stocks by supplier ID.
     *
     * @return Collection<int, Stock>
     */
    public function findBySupplier(string $supplierId): Collection
    {
        return Stock::with(['company:id,name', 'supplier:id,name'])
            ->where('supplier_id', $supplierId)
            ->get();
    }
}
