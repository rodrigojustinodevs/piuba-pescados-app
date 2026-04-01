<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Supply;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SupplyRepositoryInterface;

final class SupplyRepository implements SupplyRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'company:id,name',
    ];

    /**
     * @param array{
     *     company_id?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $paginator = Supply::with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['company_id']),
                static fn ($q) => $q->where('company_id', $filters['company_id']),
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): Supply
    {
        return Supply::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }
}
