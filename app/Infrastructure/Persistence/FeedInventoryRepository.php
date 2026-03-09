<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\FeedInventory;
use App\Domain\Repositories\FeedInventoryRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedInventoryRepository implements FeedInventoryRepositoryInterface
{
    /**
     * Create a new feed inventory record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): FeedInventory
    {
        return FeedInventory::create($data);
    }

    /**
     * Update an existing feed inventory record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?FeedInventory
    {
        $feedInventory = FeedInventory::find($id);

        if ($feedInventory) {
            $feedInventory->update($data);

            return $feedInventory;
        }

        return null;
    }

    /**
     * Get paginated feed inventory records.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<int, FeedInventory> $paginator */
        $paginator = FeedInventory::with([
            'company:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show feed inventory by field and value.
     */
    public function showFeedInventory(string $field, string | int $value): ?FeedInventory
    {
        return FeedInventory::where($field, $value)->first();
    }

    /**
     * Find feed inventory by company and feed type.
     */
    public function findByCompanyAndFeedType(string $companyId, string $feedType): ?FeedInventory
    {
        return FeedInventory::where('company_id', $companyId)
            ->where('feed_type', $feedType)
            ->first();
    }

    public function delete(string $id): bool
    {
        $feedInventory = FeedInventory::find($id);

        if (! $feedInventory) {
            return false;
        }

        return (bool) $feedInventory->delete();
    }
}
