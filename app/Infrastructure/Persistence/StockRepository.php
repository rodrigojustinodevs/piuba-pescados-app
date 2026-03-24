<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\StockInputDTO;
use App\Domain\Models\Stock;
use App\Domain\Models\Supply;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use Illuminate\Support\Collection;
use RuntimeException;

class StockRepository implements StockRepositoryInterface
{
    /**
     * Create a new stock.
     */
    public function create(StockInputDTO $dto): Stock
    {
        $supplyId = $dto->supplyId;

        if ($supplyId === null || $supplyId === '') {
            $supply = Supply::create([
                'company_id'   => $dto->companyId,
                'name'         => 'Estoque legado ' . substr((string) \Illuminate\Support\Str::uuid(), 0, 8),
                'default_unit' => $dto->unit,
            ]);
            $supplyId = $supply->id;
        }

        /** @var Stock */
        return Stock::create([
            'company_id'          => $dto->companyId,
            'supply_id'           => $supplyId,
            'supplier_id'         => $dto->supplierId,
            'current_quantity'    => $dto->quantity,
            'unit'                => $dto->unit,
            'unit_price'          => $dto->unitPrice,
            'minimum_stock'       => $dto->minimumStock,
            'withdrawal_quantity' => $dto->withdrawalQuantity,
        ]);
    }

    /**
     * Update an existing stock.
     *
     */
    public function update(string $id, array $attributes): Stock
    {
        $stock = $this->findOrFail($id);

        $stock->update(array_filter($attributes, static fn ($v): bool => $v !== null));

        return $stock->refresh();
    }

    /**
     * Get paginated .
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = Stock::with(['supply:id,name,default_unit', 'supplier:id,name'])
            ->where('company_id', $filters['company_id'])
            ->when(
                ! empty($filters['supply_id']),
                static fn ($q) => $q->where('supply_id', $filters['supply_id']),
            )
            ->when(
                ! empty($filters['supplier_id']),
                static fn ($q) => $q->where('supplier_id', $filters['supplier_id']),
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 25));

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

    public function findOrFail(string $id): Stock
    {
        return Stock::with(['company:id,name', 'supplier:id,name'])
            ->findOrFail($id);
    }

    public function findBySupply(string $companyId, string $supplyId): ?Stock
    {
        return Stock::where('company_id', $companyId)
            ->where('supply_id', $supplyId)
            ->first();
    }

    public function findByCompanyAndSupplier(string $companyId, string $supplierId): ?Stock
    {
        return Stock::where('company_id', $companyId)
            ->where('supplier_id', $supplierId)
            ->first();
    }

    public function findByCompanyAndSupply(string $companyId, string $supplyId): ?Stock
    {
        return Stock::where('company_id', $companyId)
            ->where('supply_id', $supplyId)
            ->first();
    }

    public function getUnitPriceByStockId(string $stockId): float
    {
        $unitPrice = Stock::where('id', $stockId)->value('unit_price');

        if (! $unitPrice || ! is_numeric($unitPrice)) {
            throw new RuntimeException('Unit price not found');
        }

        return (float) $unitPrice;
    }

    public function incrementQuantity(string $id, float $quantity): Stock
    {
        $stock = $this->findOrFail($id);

        $stock->increment('current_quantity', $quantity);

        return $stock->refresh();
    }

    public function decrementQuantity(string $id, float $quantity): Stock
    {
        $stock = $this->findOrFail($id);

        $stock->decrement('current_quantity', $quantity);

        return $stock->refresh();
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    /**
     * @return Collection<int, Stock>
     */
    public function findBySupplier(string $supplierId): Collection
    {
        $items = Stock::with(['company:id,name', 'supplier:id,name'])
            ->where('supplier_id', $supplierId)
            ->get();

        return new Collection($items->all());
    }

    /** @return array<int, string> */
    public function getLowStockAlerts(string $companyId): array
    {
        return Stock::query()
            ->join('supplies', 'supplies.id', '=', 'stocks.supply_id')
            ->where('stocks.company_id', $companyId)
            ->whereColumn('stocks.current_quantity', '<', 'stocks.minimum_stock')
            ->whereNull('stocks.deleted_at')
            ->whereNull('supplies.deleted_at')
            ->pluck('supplies.name')
            ->toArray();
    }

    public function countStocksBelowMinimum(string $companyId): int
    {
        return Stock::where('company_id', $companyId)
            ->whereColumn('current_quantity', '<', 'minimum_stock')
            ->whereNull('deleted_at')
            ->count();
    }
}
