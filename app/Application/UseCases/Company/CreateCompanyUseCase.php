<?php

declare(strict_types=1);

namespace App\Application\UseCases\Company;

use App\Application\DTOs\CompanyDTO;
use App\Domain\Enums\Status;
use App\Domain\Repositories\CompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateCompanyUseCase
{
    public function __construct(
        protected CompanyRepositoryInterface $companyRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): CompanyDTO
    {
        return DB::transaction(function () use ($data): CompanyDTO {
            $company = $this->companyRepository->create($data);

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
        });
    }
}
