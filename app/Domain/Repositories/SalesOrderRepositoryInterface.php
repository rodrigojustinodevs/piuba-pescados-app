<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\SalesOrderDTO;
use App\Application\DTOs\SalesOrderItemDTO;
use App\Application\DTOs\SalesQuotationDTO;
use App\Domain\Models\SalesOrder;

interface SalesOrderRepositoryInterface
{
    public function findOrFail(string $id): SalesOrder;

    /**
     * Order/quotation of the company with default relations (404 if not exists or is of another company).
     */
    public function findForCompanyOrFail(string $id, string $companyId): SalesOrder;

    /**
     * Persist the order and its items.
     * No DB::transaction here — the transaction is responsibility of the UseCase.
     */
    public function createWithItems(SalesOrderDTO $dto): SalesOrder;

    /**
     * Persist the quotation (default type/status of quotation) and items in bulk insert.
     */
    public function createQuotationWithItems(SalesQuotationDTO $dto): SalesOrder;

    /**
     * Sync the collection of persisted items with the received DTOs:
     * - remove items missing in the DTOs
     * - update existing items
     * - create new items
     *
     * @param SalesOrderItemDTO[] $itemDTOs
     */
    public function syncItems(SalesOrder $order, array $itemDTOs): void;

    /**
     * Replaces all persisted items with the received DTO collection.
     *
     * @param SalesOrderItemDTO[] $itemDTOs
     */
    public function replaceItems(SalesOrder $order, array $itemDTOs): void;

    /**
     * Update only scalar attributes of the header (without replacing items).
     *
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): SalesOrder;

    /**
     * @param array{
     *     companyId: string,
     *     clientId?: string|null,
     *     status?: string|null,
     *     type?: string|null,
     *     perPage?: int,
     *     page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;
}
