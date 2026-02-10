<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Company;
use App\Domain\Repositories\CompanyRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CompanyRepository implements CompanyRepositoryInterface
{
    /**
     * Create a new company.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Company
    {
        return Company::create($data);
    }

    /**
     * Update an existing company.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Company
    {
        $company = Company::find($id);

        if ($company) {
            $company->update($data);

            return $company;
        }

        return null;
    }

    /**
     * Get paginated companies with optional search by name, cnpj and email.
     */
    public function paginate(int $perPage = 25, ?string $search = null): PaginationInterface
    {
        $query = Company::query();

        if ($search !== null && $search !== '') {
            $term = '%' . $search . '%';
            $query->whereAny(['name', 'cnpj', 'email'], 'like', $term);
        }

        /** @var LengthAwarePaginator<Company> $paginator */
        $paginator = $query->paginate($perPage);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show company by field and value.
     */
    public function showCompany(string $field, string | int $value): ?Company
    {
        return Company::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $company = Company::find($id);

        if (! $company) {
            return false;
        }

        return (bool) $company->delete();
    }
}
