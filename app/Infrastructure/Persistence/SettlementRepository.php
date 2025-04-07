<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Settlement;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SettlementRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SettlementRepository implements SettlementRepositoryInterface
{
    /**
     * Create a new settlement.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Settlement
    {
        return Settlement::create($data);
    }

    /**
     * Update an existing settlement.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Settlement
    {
        $settlement = Settlement::find($id);

        if ($settlement) {
            $settlement->update($data);

            return $settlement;
        }

        return null;
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<Settlement> $paginator */
        $paginator = Settlement::with([
            'batche:id',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show settlement by field and value.
     */
    public function showSettlement(string $field, string | int $value): ?Settlement
    {
        return Settlement::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $settlement = Settlement::find($id);

        if (! $settlement) {
            return false;
        }

        return (bool) $settlement->delete();
    }
}
