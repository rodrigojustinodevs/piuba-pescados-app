<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Stock;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockRepositoryInterface;
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
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show stock by field and value.
     */
    public function showStock(string $field, string | int $value): ?Stock
    {
        return Stock::where($field, $value)->first();
    }

    /**
     * Find stock by company and supply name.
     */
    public function findByCompanyAndSupplyName(string $companyId, string $supplyName): ?Stock
    {
        return Stock::where('company_id', $companyId)
            ->where('supply_name', $supplyName)
            ->first();
    }

    /**
     * Get unit price for a stock by company and supply name.
     */
    public function getUnitPrice(string $companyId, string $supplyName): float
    {
        $unitPrice = Stock::select('unit_price')->where('company_id', $companyId)
            ->where('supply_name', $supplyName)
            ->value('unit_price');

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
}
