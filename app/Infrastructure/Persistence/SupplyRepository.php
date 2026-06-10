<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\SupplyInputDTO;
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
     *     companyId?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $paginator = Supply::with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['companyId']),
                static fn ($q) => $q->where('company_id', $filters['companyId']),
            )
            ->latest()
            ->paginate((int) ($filters['perPage'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): Supply
    {
        return Supply::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    public function create(SupplyInputDTO $dto): Supply
    {
        /** @var Supply $supply */
        $supply = Supply::create($dto->toPersistence());

        return $supply->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Supply
    {
        $supply = $this->findOrFail($id);
        $supply->update($attributes);

        /** @var Supply $refreshed */
        $refreshed = $supply->refresh();

        return $refreshed->load(self::DEFAULT_RELATIONS);
    }
}
