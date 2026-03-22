<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\InventoryAdjustment;

interface InventoryAdjustmentRepositoryInterface
{
    /**
     * @param array{
     *     stock_id: string,
     *     company_id: string,
     *     user_id: string,
     *     previous_quantity: float,
     *     new_quantity: float,
     *     adjusted_quantity: float,
     *     unit: string,
     *     unit_price: float,
     *     status: string,
     *     reason: string|null,
     * } $attributes
     */
    public function create(array $attributes): InventoryAdjustment;

    /**
     * Vincula a transação gerada ao documento de ajuste.
     */
    public function linkTransaction(
        InventoryAdjustment $adjustment,
        string $transactionId,
    ): InventoryAdjustment;

    /**
     * Marca o ajuste como aplicado.
     */
    public function markAsApplied(InventoryAdjustment $adjustment): InventoryAdjustment;

    /**
     * @param array{
     *     stock_id?: string|null,
     *     company_id: string,
     *     status?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;
}