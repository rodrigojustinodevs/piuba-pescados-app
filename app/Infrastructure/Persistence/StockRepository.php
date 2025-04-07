<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Stock;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

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
        /** @var LengthAwarePaginator<Stock> $paginator */
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

    public function delete(string $id): bool
    {
        $stock = Stock::find($id);

        if (! $stock) {
            return false;
        }

        return (bool) $stock->delete();
    }
}
