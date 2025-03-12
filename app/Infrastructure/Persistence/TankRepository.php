<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

class TankRepository implements TankRepositoryInterface
{
    /**
     * Create a new tank.
     *
     * @param array<string, mixed> $data
     * @return Tank
     */
    public function create(array $data): Tank
    {
        return Tank::create($data);
    }

    /**
     * Update an existing tank.
     *
     * @param string $id
     * @param array<string, mixed> $data
     * @return Tank|null
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
     * Get paginated companies.
     *
     * @param int $page
     * @return PaginationInterface
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        return new PaginationPresentr($tanks = Tank::with([
            'tankType:id,name',
            'company:id,name'
        ])->paginate(25));
    }

    /**
     * Show tank by field and value.
     *
     * @param string $field
     * @param string|int $value
     * @return Tank|null
     */
    public function showTank(string $field, string|int $value): ?Tank
    {
        return Tank::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $tank = Tank::find($id);

        if (!$tank) {
            return false;
        }

        return (bool) $tank->delete();
    }
}
