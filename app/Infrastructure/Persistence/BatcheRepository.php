<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Batche;
use App\Domain\Repositories\BatcheRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class BatcheRepository implements BatcheRepositoryInterface
{
    /**
     * Create a new batche.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Batche
    {
        return Batche::create($data);
    }

    /**
     * Update an existing batche.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Batche
    {
        $batche = Batche::find($id);

        if ($batche) {
            $batche->update($data);

            return $batche;
        }

        return null;
    }

    /**
     * Get paginated .
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<Batche> $paginator */
        $paginator = Batche::with([
            'tank:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show batche by field and value.
     */
    public function showBatche(string $field, string | int $value): ?Batche
    {
        return Batche::with([
            'tank:id,name',
        ])->where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $batche = Batche::find($id);

        if (! $batche) {
            return false;
        }

        return (bool) $batche->delete();
    }
}
