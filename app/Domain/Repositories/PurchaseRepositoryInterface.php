<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\PurchaseDTO;
use App\Application\DTOs\PurchaseItemDTO;
use App\Domain\Models\Purchase;

interface PurchaseRepositoryInterface
{
    public function create(PurchaseDTO $dto): Purchase;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Purchase;

    /**
     * @param PurchaseItemDTO[] $itemDTOs
     */
    public function syncItems(Purchase $purchase, array $itemDTOs): void;

    public function delete(string $id): bool;

    /**
     * @param array{
     *     companyId: string,
     *     status?: string|null,
     *     paymentStatus?: string|null,
     *     paymentMethod?: string|null,
     *     supplierId?: string|null,
     *     code?: string|null,
     *     dateFrom?: string|null,
     *     dateTo?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;

    public function showPurchase(string $field, string | int $value): ?Purchase;

    public function findOrFail(string $id): Purchase;
}
