<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Feeding;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedingRepository implements FeedingRepositoryInterface
{
    /**
     * Create a new feeding.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Feeding
    {
        return Feeding::create($data);
    }

    /**
     * Update an existing feeding.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Feeding
    {
        $feeding = Feeding::find($id);

        if ($feeding) {
            $feeding->update($data);

            return $feeding;
        }

        return null;
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<Feeding> $paginator */
        $paginator = Feeding::with([
            'batche:id',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show feeding by field and value.
     */
    public function showFeeding(string $field, string | int $value): ?Feeding
    {
        return Feeding::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $feeding = Feeding::find($id);

        if (! $feeding) {
            return false;
        }

        return (bool) $feeding->delete();
    }
}
