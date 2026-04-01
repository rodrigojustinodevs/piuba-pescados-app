<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\CompanyInputDTO;
use App\Domain\Models\Company;
use App\Domain\Repositories\CompanyRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

final class CompanyRepository implements CompanyRepositoryInterface
{
    public function create(CompanyInputDTO $dto): Company
    {
        /** @var Company $company */
        $company = Company::create($dto->toPersistence());

        return $company;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Company
    {
        $company = $this->findOrFail($id);

        if ($attributes !== []) {
            $company->update($attributes);
            $company->refresh();
        }

        return $company;
    }

    /**
     * @param array{
     *     per_page?: int,
     *     search?: string|null,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $search  = $filters['search'] ?? null;
        $perPage = (int) ($filters['per_page'] ?? 25);

        $query = Company::query()
            ->when(
                is_string($search) && $search !== '',
                static fn ($q) => $q->whereAny(['name', 'cnpj', 'email'], 'like', '%' . $search . '%'),
            )
            ->latest();

        /** @var LengthAwarePaginator<int, Company> $paginator */
        $paginator = $query->paginate($perPage);

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): Company
    {
        return Company::query()->findOrFail($id);
    }

    public function showCompany(string $field, string | int $value): ?Company
    {
        return Company::query()->where($field, $value)->first();
    }

    public function delete(string $id): void
    {
        $this->findOrFail($id)->delete();
    }
}
