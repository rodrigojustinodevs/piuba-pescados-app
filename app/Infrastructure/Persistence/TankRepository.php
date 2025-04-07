<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Tank;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TankRepository implements TankRepositoryInterface
{
    /**
     * Create a new tank.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Tank
    {
        return Tank::create($data);
    }

    /**
     * Update an existing tank.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Tank
    {
        $tank = Tank::find($id);

        if ($tank) {
            $tank->update($data);

            return $tank;
        }

        return null;
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<Tank> $paginator */
        $paginator = Tank::with([
            'tankType:id,name',
            'company:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show tank by field and value.
     */
    public function showTank(string $field, string | int $value): ?Tank
    {
        return Tank::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $tank = Tank::find($id);

        if (! $tank) {
            return false;
        }

        return (bool) $tank->delete();
    }
}
