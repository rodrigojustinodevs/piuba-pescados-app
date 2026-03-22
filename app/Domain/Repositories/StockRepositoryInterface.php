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
     *
     * @param StockInputDTO $dto
     * @return Stock
     */
    public function  create(StockInputDTO $dto): Stock;

    /**
     * Update an existing feeding record.
     *
     * @param array<string, mixed> $attributes
     * @return Stock
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
     * @return PaginationInterface
     */
    public function paginate(array $filters): PaginationInterface;

    /**
     * Find a stock by a specific field.
     * @param string $field
     * @param string|int $value
     * @return Stock|null
     */
    public function showStock(string $field, string | int $value): ?Stock;

    /**
     * Find a stock by ID.
     * @param string $id
     * @return Stock
     */
    public function findOrFail(string $id): Stock;

    /**
     * Find first stock by company and supplier.
     * @param string $companyId
     * @param string $supplierId
     * @return Stock|null
     */
    public function findByCompanyAndSupplier(string $companyId, string $supplierId): ?Stock;

    /**
     * Find first stock by company and supply.
     * @param string $companyId
     * @param string $supplyId
     * @return Stock|null
     */
    public function findBySupply(string $companyId, string $supplyId): ?Stock;

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
