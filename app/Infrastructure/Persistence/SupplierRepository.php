<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Supplier;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SupplierRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SupplierRepository implements SupplierRepositoryInterface
{
    /**
     * Create a new supplier.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    /**
     * Update an existing supplier.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Supplier
    {
        $supplier = Supplier::find($id);

        if ($supplier) {
            $supplier->update($data);

            return $supplier;
        }

        return null;
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<Supplier> $paginator */
        $paginator = Supplier::with([
            'company:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show supplier by field and value.
     */
    public function showSupplier(string $field, string | int $value): ?Supplier
    {
        return Supplier::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $supplier = Supplier::find($id);

        if (! $supplier) {
            return false;
        }

        return (bool) $supplier->delete();
    }
}
