<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\CompanyDTO;
use App\Application\UseCases\Company\CreateCompanyUseCase;
use App\Application\UseCases\Company\DeleteCompanyUseCase;
use App\Application\UseCases\Company\ShowAllCompaniesUseCase;
use App\Application\UseCases\Company\ShowCompanyUseCase;
use App\Application\UseCases\Company\UpdateCompanyUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompanyService
{
    public function __construct(
        protected CreateCompanyUseCase $createCompanyUseCase,
        protected UpdateCompanyUseCase $updateCompanyUseCase,
        protected ShowCompanyUseCase $showCompanyUseCase,
        protected ShowAllCompaniesUseCase $showAllCompaniesUseCase,
        protected DeleteCompanyUseCase $deleteCompanyUseCase,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): CompanyDTO
    {
        return $this->createCompanyUseCase->execute($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateCompany(string $id, array $data): CompanyDTO
    {
        return $this->updateCompanyUseCase->execute($id, $data);
    }

    public function showCompany(string $id): ?CompanyDTO
    {
        return $this->showCompanyUseCase->execute($id);
    }

    /**
     * @param int         $limit Items per page.
     * @param string|null $search Optional search (filters by name, cnpj, email).
     */
    public function showAllCompanies(int $limit = 25, ?string $search = null): AnonymousResourceCollection
    {
        return $this->showAllCompaniesUseCase->execute($limit, $search);
    }

    public function deleteCompany(string $id): bool
    {
        return $this->deleteCompanyUseCase->execute($id);
    }
}
