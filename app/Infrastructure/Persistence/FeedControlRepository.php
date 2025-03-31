<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\FeedControl;
use App\Domain\Repositories\FeedControlRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedControlRepository implements FeedControlRepositoryInterface
{
    /**
     * Create a new feedControl.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): FeedControl
    {
        return FeedControl::create($data);
    }

    /**
     * Update an existing feedControl.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?FeedControl
    {
        $feedControl = FeedControl::find($id);

        if ($feedControl) {
            $feedControl->update($data);

            return $feedControl;
        }

        return null;
    }

    /**
     * Get paginated companies.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<FeedControl> $paginator */
        $paginator = FeedControl::with([
            'company:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show feedControl by field and value.
     */
    public function showFeedControl(string $field, string | int $value): ?FeedControl
    {
        return FeedControl::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $feedControl = FeedControl::find($id);

        if (! $feedControl) {
            return false;
        }

        return (bool) $feedControl->delete();
    }
}
