<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Company;
use App\Domain\Repositories\AbstractRepository;
use App\Domain\Repositories\CompanyRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

class CompanyRepository extends AbstractRepository implements CompanyRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Company());
    }

    public function create(array $data): Company
    {
        return Company::create($data);
    }

    public function update(string $id, array $data): ?Company
    {
        $company = Company::find($id);
        if ($company) {
            $company->update($data);
            return $company;
        }
        return null;
    }

    public function findAll(): array
    {
        return $this->getModel()->query()->get()->toArray();
    }

    public function paginate(int $page = 25): PaginationInterface
    {
        return new PaginationPresentr($this->getModel()::paginate($page));
    }

    public function showCompany(string $field, string|int $value): ?Company
    {
        $result = $this->getModel()->query()->where($field, $value)->first();
        return $result;
    }
}
