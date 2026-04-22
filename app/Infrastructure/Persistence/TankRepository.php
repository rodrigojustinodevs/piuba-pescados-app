<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\TankInputDTO;
use App\Domain\Models\Tank;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

final class TankRepository implements TankRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'tankType:id,name',
        'company:id,name',
    ];

    /**
     * @param array{
     *     companyId?: string|null,
     *     name?: string|null,
     *     tankTypeId?: string|null,
     *     status?: string|null,
     *     perPage?: int,
     *     search?: string|null,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $search  = $filters['search'] ?? null;
        $perPage = (int) ($filters['perPage'] ?? 25);

        $paginator = Tank::with(self::DEFAULT_RELATIONS)
            ->when(
                is_string($search) && $search !== '',
                static fn ($q) => $q->whereAny(['name', 'location'], 'like', '%' . $search . '%'),
            )
            ->when(
                ! empty($filters['companyId']),
                static fn ($q) => $q->where('company_id', $filters['companyId']),
            )
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where('status', $filters['status']),
            )
            ->latest()
            ->paginate($perPage);

        /** @var LengthAwarePaginator<int, Tank> $paginator */
        return new PaginationPresentr($paginator);
    }

    /**
     * @param array{
     *     company_id?: string|null,
     *     status?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginateWithoutBatches(array $filters = []): PaginationInterface
    {
        $paginator = Tank::with(self::DEFAULT_RELATIONS)
            ->whereDoesntHave('batches')
            ->when(
                ! empty($filters['company_id']),
                static fn ($q) => $q->where('company_id', $filters['company_id']),
            )
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where('status', $filters['status']),
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): Tank
    {
        return Tank::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    public function showTank(string $field, string | int $value): ?Tank
    {
        return Tank::with(self::DEFAULT_RELATIONS)->where($field, $value)->first();
    }

    public function create(TankInputDTO $dto): Tank
    {
        /** @var Tank $tank */
        $tank = Tank::create($dto->toPersistence());

        return $tank->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Tank
    {
        $tank = $this->findOrFail($id);
        $tank->update($attributes);

        return $tank->refresh();
    }

    public function delete(string $id): void
    {
        $this->findOrFail($id)->delete();
    }

    /** @return array<int, array<string, mixed>> */
    public function findAllByCompany(string $companyId): array
    {
        return Tank::where('company_id', $companyId)->get()->toArray();
    }

    public function countActiveTanks(string $companyId): int
    {
        return Tank::where('company_id', $companyId)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->count();
    }
}
