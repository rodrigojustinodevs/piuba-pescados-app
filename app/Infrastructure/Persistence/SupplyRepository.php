<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\SupplyInputDTO;
use App\Domain\Models\Supply;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SupplyRepositoryInterface;

final class SupplyRepository implements SupplyRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'company:id,name',
        'supplier:id,name,contact,phone,email',
    ];

    /**
     * @param array{
     *     companyId?: string|null,
     *     perPage?: int,
     *     category?: string|null,
     *     status?: string|null,
     *     isProduct?: bool|null,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $paginator = Supply::with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['companyId']),
                static fn ($q) => $q->where('company_id', $filters['companyId']),
            )
            ->when(
                ! empty($filters['category']),
                static fn ($q) => $q->where('category', $filters['category']),
            )
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where('status', $filters['status']),
            )
            ->when(
                isset($filters['isProduct']),
                static fn ($q) => $q->where('is_product', $filters['isProduct']),
            )
            ->latest()
            ->paginate((int) ($filters['perPage'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): Supply
    {
        return Supply::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    public function create(SupplyInputDTO $dto): Supply
    {
        /** @var Supply $supply */
        $supply = Supply::create($dto->toPersistence());

        return $supply->load(self::DEFAULT_RELATIONS);
    }

    public function update(string $id, SupplyInputDTO $dto): Supply
    {
        $supply = $this->findOrFail($id);
        $supply->fill($dto->toPersistence());
        $supply->save();

        /** @var Supply $refreshed */
        $refreshed = $supply->refresh();

        return $refreshed->load(self::DEFAULT_RELATIONS);
    }

    public function delete(string $id): bool
    {
        $supply = $this->findOrFail($id);

        return (bool) $supply->delete();
    }
}
