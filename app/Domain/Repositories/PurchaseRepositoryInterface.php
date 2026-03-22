<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Purchase;
use App\Application\DTOs\PurchaseDTO;
use App\Application\DTOs\PurchaseItemDTO;

interface PurchaseRepositoryInterface
{
    /**
     * Create a new purchase record.
     *
     */
    public function create(PurchaseDTO $dto): Purchase;

    /**
     * Update an existing purchase record.
     *
     */
    public function update(string $id, array $attributes): Purchase;

    /**
     * Sync the collection of persisted items with the received DTOs:
     * - remove items missing in the DTOs
     * - update existing items
     * - create new items
     *
     * @param PurchaseItemDTO[] $itemDTOs
     */
    public function syncItems(Purchase $purchase, array $itemDTOs): void;

    /**
     * Delete a purchase record.
    */
    public function delete(string $id): bool;

    /**
     * @param array{
     *     company_id: string,
     *     status?: string|null,
     *     supplier_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;

    /**
     * Find a purchase by a specific field.
     */
    public function showPurchase(string $field, string | int $value): ?Purchase;

    /**
     * Find a purchase by ID.
     */
    public function findOrFail(string $id): Purchase;
}
