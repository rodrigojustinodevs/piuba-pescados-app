<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Sale;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SaleRepository implements SaleRepositoryInterface
{
    /**
     * Create a new sale.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Sale
    {
        return Sale::create($data);
    }

    /**
     * Update an existing sale.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Sale
    {
        $sale = Sale::find($id);

        if ($sale) {
            $sale->update($data);

            return $sale;
        }

        return null;
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<int, Sale> $paginator */
        $paginator = Sale::with([
            'client:id,name',
            'company:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show sale by field and value.
     */
    public function showSale(string $field, string | int $value): ?Sale
    {
        return Sale::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $sale = Sale::find($id);

        if (! $sale) {
            return false;
        }

        return (bool) $sale->delete();
    }
}
