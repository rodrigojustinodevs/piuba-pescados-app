<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\StockInputDTO;
use App\Domain\Models\Stock;
use Illuminate\Support\Collection;

interface StockRepositoryInterface
{
    /**
     * Create a new stock record.
     */
    public function create(StockInputDTO $dto): Stock;

    /**
     * Update an existing feeding record.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Stock;

    /**
     * Delete a stock record.
     */
    public function delete(string $id): bool;

    /**
     * @param array{
     *     company_id: string,
     *     supply_id?: string|null,
     *     supplier_id?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;

    /**
     * Find a stock by a specific field.
     */
    public function showStock(string $field, string | int $value): ?Stock;

    /**
     * Find a stock by ID.
     */
    public function findOrFail(string $id): Stock;

    /**
     * Find first stock by company and supplier.
     */
    public function findByCompanyAndSupplier(string $companyId, string $supplierId): ?Stock;

    /**
     * Find first stock by company and supply.
     */
    public function findBySupply(string $companyId, string $supplyId): ?Stock;

    /**
     * Alias for findBySupply. Find first stock by company and supply.
     */
    public function findByCompanyAndSupply(string $companyId, string $supplyId): ?Stock;

    /**
     * Find stocks by supplier ID.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Stock>
     * @return Collection<int, Stock>
     */
    public function findBySupplier(string $supplierId): Collection;

    /**
     * Get unit price for a stock by ID.
     */
    public function getUnitPriceByStockId(string $stockId): float;

    /**
     * Increment current_quantity atomically (stock entry).
     */
    public function incrementQuantity(string $id, float $quantity): Stock;

    /**
     * Decrement current_quantity atomically (stock exit).
     */
    public function decrementQuantity(string $id, float $quantity): Stock;
}
