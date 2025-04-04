<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Mortality;
use App\Domain\Repositories\MortalityRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class MortalityRepository implements MortalityRepositoryInterface
{
    /**
     * Create a new mortality.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Mortality
    {
        return Mortality::create($data);
    }

    /**
     * Update an existing mortality.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Mortality
    {
        $mortality = Mortality::find($id);

        if ($mortality) {
            $mortality->update($data);

            return $mortality;
        }

        return null;
    }

    /**
     * Get paginated companies.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<Mortality> $paginator */
        $paginator = Mortality::with([
            'batche:id',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show mortality by field and value.
     */
    public function showMortality(string $field, string | int $value): ?Mortality
    {
        return Mortality::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $mortality = Mortality::find($id);

        if (! $mortality) {
            return false;
        }

        return (bool) $mortality->delete();
    }
}
