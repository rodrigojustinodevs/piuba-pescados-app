<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\CompanyDTO;
use App\Domain\Enums\Status;
use App\Domain\Models\Company;
use App\Domain\Repositories\CompanyRepositoryInterface;
use App\Presentation\Resources\Company\CompanyResource;
use Illuminate\Support\Facades\DB;
use Throwable;

class CompanyService
{
    public function __construct(
        protected CompanyRepositoryInterface $companyRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): CompanyDTO
    {
        return DB::transaction(function () use ($data): CompanyDTO {
            $company = $this->companyRepository->create($data);
            return $this->mapToDTO($company);
        });
    }

    public function showAllCompanies()
    {
        $response = $this->companyRepository->paginate();

        return CompanyResource::collection($response->items())
            ->additional([
                'pagination' => [
                    'total'        => $response->total(),
                    'current_page' => $response->currentPage(),
                    'last_page'    => $response->lastPage(),
                    'first_page'   => $response->firstPage(),
                    'per_page'     => $response->perPage(),
                ]
            ]);
    }

    /**
     * Returns the details of a company.
     */
    public function showCompany(string $id): CompanyDTO
    {
        $company = $this->companyRepository->showCompany('id', $id);

        return $this->mapToDTO($company);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateCompany(string $id, array $data): CompanyDTO
    {
        return DB::transaction(function () use ($id, $data): CompanyDTO {
            $company = $this->companyRepository->update($id, $data);

            if (!$company) {
                throw new \Exception('Company not found');
            }

            return $this->mapToDTO($company);
        });
    }

    public function deleteCompany(string $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            return $this->companyRepository->delete(['id' => $id]);
        });
    }

    private function mapToDTO(Company $company): CompanyDTO
    {
        return new CompanyDTO(
            id: $company->id,
            name: $company->name,
            cnpj: $company->cnpj,
            address: $company->address,
            phone: $company->phone,
            status: Status::from($company->status),
            createdAt: $company->created_at ? $company->created_at->toDateTimeString() : null,
            updatedAt: $company->updated_at ? $company->updated_at->toDateTimeString() : null
        );
    }
}
