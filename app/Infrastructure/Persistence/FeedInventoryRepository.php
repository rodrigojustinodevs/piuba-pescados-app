<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\FeedInventory;
use App\Domain\Repositories\FeedInventoryRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final class FeedInventoryRepository implements FeedInventoryRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'company:id,name',
    ];

    /**
     * @param array{
     *     company_id?: string|null,
     *     feed_type?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $paginator = FeedInventory::with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['company_id']),
                static fn ($q) => $q->where('company_id', $filters['company_id']),
            )
            ->when(
                ! empty($filters['feed_type']),
                static fn ($q) => $q->where('feed_type', $filters['feed_type']),
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): FeedInventory
    {
        return FeedInventory::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): FeedInventory
    {
        /** @var FeedInventory $feedInventory */
        $feedInventory = FeedInventory::create($data);

        return $feedInventory->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): FeedInventory
    {
        $feedInventory = $this->findOrFail($id);
        $feedInventory->update($attributes);

        return $feedInventory->refresh();
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function findByCompanyAndFeedType(string $companyId, string $feedType): ?FeedInventory
    {
        return FeedInventory::where('company_id', $companyId)
            ->where('feed_type', $feedType)
            ->first();
    }
}
