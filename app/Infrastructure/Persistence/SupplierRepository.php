<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\SupplierInputDTO;
use App\Domain\Models\Supplier;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SupplierRepositoryInterface;

final class SupplierRepository implements SupplierRepositoryInterface
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
        $paginator = Supplier::with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['company_id']),
                static fn ($q) => $q->where('company_id', $filters['company_id']),
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): Supplier
    {
        return Supplier::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    public function showSupplier(string $field, string | int $value): ?Supplier
    {
        return Supplier::with(self::DEFAULT_RELATIONS)->where($field, $value)->first();
    }

    public function create(SupplierInputDTO $dto): Supplier
    {
        /** @var Supplier $supplier */
        $supplier = Supplier::create($dto->toPersistence());

        return $supplier->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Supplier
    {
        $supplier = $this->findOrFail($id);
        $supplier->update($attributes);

        return $supplier->refresh();
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}
