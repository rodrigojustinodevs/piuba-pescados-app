<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Purchase;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchaseRepository implements PurchaseRepositoryInterface
{
    /**
     * Create a new purchase.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Purchase
    {
        return Purchase::create($data);
    }

    /**
     * Update an existing purchase.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Purchase
    {
        $purchase = Purchase::find($id);

        if ($purchase) {
            $purchase->update($data);

            return $purchase;
        }

        return null;
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<Purchase> $paginator */
        $paginator = Purchase::with([
            'supplier:id,name',
            'company:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show purchase by field and value.
     */
    public function showPurchase(string $field, string | int $value): ?Purchase
    {
        return Purchase::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $purchase = Purchase::find($id);

        if (! $purchase) {
            return false;
        }

        return (bool) $purchase->delete();
    }
}
