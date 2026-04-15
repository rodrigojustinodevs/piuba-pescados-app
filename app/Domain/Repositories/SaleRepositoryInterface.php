<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\SaleInputDTO;
use App\Domain\Models\Sale;
use Illuminate\Support\Collection;

interface SaleRepositoryInterface
{
    /**
     * Create a new sale record.
     */
    public function create(SaleInputDTO $dto): Sale;

    /**
     * Update an existing sale record.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Sale;

    /**
     * Delete a sale record (soft delete).
     */
    public function delete(string $id): bool;

    /**
     * Find a sale by ID or throw ModelNotFoundException.
     */
    public function findOrFail(string $id): Sale;

    /**
     * Paginate sales filtered by company.
     *
     * @param array{
     *     company_id: string,
     *     client_id?: string|null,
     *     batch_id?: string|null,
     *     status?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;

    /**
     * Total kg already sold from a given stocking (excluding soft-deleted and cancelled records).
     * Pass $excludeSaleId to omit the current sale when re-validating on update.
     */
    public function soldWeightByStocking(string $stockingId, ?string $excludeSaleId = null): float;

    /**
     * Busca a venda aplicando lockForUpdate (lock pessimista).
     * Usado pelo UpdateSaleUseCase para evitar edições concorrentes na mesma venda.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFailLocked(string $id): Sale;

    /**
     * Find sales by sales order ID.
     *
     * @return Collection<int, Sale>
     */
    public function findByOrderId(string $orderId): Collection;
}
