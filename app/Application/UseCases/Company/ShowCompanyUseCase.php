<?php

declare(strict_types=1);

namespace App\Application\UseCases\Company;

use App\Application\DTOs\CompanyDTO;
use App\Domain\Enums\Status;
use App\Domain\Models\Company;
use App\Domain\Repositories\CompanyRepositoryInterface;
use RuntimeException;

class ShowCompanyUseCase
{
    public function __construct(
        protected CompanyRepositoryInterface $companyRepository
    ) {
    }

    public function execute(string $id): ?CompanyDTO
    {
        $company = $this->companyRepository->showCompany('id', $id);

        if (! $company instanceof Company) {
            throw new RuntimeException('Company not found');
        }

        return new CompanyDTO(
            id: $company->id,
            name: $company->name,
            cnpj: $company->cnpj,
            address: $company->address,
            phone: $company->phone,
            status: Status::from($company->status),
            createdAt: $company->created_at?->toDateTimeString(),
            updatedAt: $company->updated_at?->toDateTimeString()
        );
    }
}
