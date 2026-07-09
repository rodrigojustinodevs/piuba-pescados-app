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
    private const array DEFAULT_RELATIONS = [
        'company:id,name',
        'supply:id,name,unit',
        'supplier:id,name',
    ];

    /**
     * Create a new stock.
     */
    public function create(StockInputDTO $dto): Stock
    {
        $supplyId = $dto->supplyId;

        if ($supplyId === null || $supplyId === '') {
            $supply = Supply::create([
                'company_id' => $dto->companyId,
                'name'       => 'Estoque legado ' . substr((string) \Illuminate\Support\Str::uuid(), 0, 8),
                'unit'       => $dto->unit,
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
            'code'                => $dto->code,
            'name'                => $dto->name,
            'type'                => $dto->type,
            'location'            => $dto->location,
            'responsible'         => $dto->responsible,
            'capacity'            => $dto->capacity,
            'status'              => $dto->status,
            'notes'               => $dto->notes,
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
     * Get paginated stocks.
     *
     * @param array{
     *     search?: string|null,
     *     companyId?: string|null,
     *     supplyId?: string|null,
     *     supplierId?: string|null,
     *     name?: string|null,
     *     code?: string|null,
     *     type?: string|null,
     *     status?: string|null,
     *     location?: string|null,
     *     responsible?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = Stock::with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['companyId']),
                static fn ($q) => $q->where('company_id', $filters['companyId']),
            )
            ->when(
                ! empty($filters['supplyId']),
                static fn ($q) => $q->where('supply_id', $filters['supplyId']),
            )
            ->when(
                ! empty($filters['supplierId']),
                static fn ($q) => $q->where('supplier_id', $filters['supplierId']),
            )
            ->when(
                ! empty($filters['name']),
                static fn ($q) => $q->where('name', 'like', '%' . $filters['name'] . '%'),
            )
            ->when(
                ! empty($filters['code']),
                static fn ($q) => $q->where('code', 'like', '%' . $filters['code'] . '%'),
            )
            ->when(
                ! empty($filters['type']),
                static fn ($q) => $q->where('type', $filters['type']),
            )
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where('status', $filters['status']),
            )
            ->when(
                ! empty($filters['location']),
                static fn ($q) => $q->where('location', 'like', '%' . $filters['location'] . '%'),
            )
            ->when(
                ! empty($filters['responsible']),
                static fn ($q) => $q->where('responsible', 'like', '%' . $filters['responsible'] . '%'),
            )
            ->latest()
            ->paginate((int) ($filters['perPage'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    /**
     * Show stock by field and value.
     */
    public function showStock(string $field, string | int $value): ?Stock
    {
        return Stock::with(self::DEFAULT_RELATIONS)
            ->where($field, $value)
            ->first();
    }

    public function findOrFail(string $id): Stock
    {
        return Stock::with(self::DEFAULT_RELATIONS)
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
        $items = Stock::with(self::DEFAULT_RELATIONS)
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

    public function findByCode(string $companyId, string $code): ?Stock
    {
        return Stock::with(self::DEFAULT_RELATIONS)
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->first();
    }
}
