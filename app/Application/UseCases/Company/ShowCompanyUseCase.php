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
            return null;
        }

        return CompanyDTO::fromArray([
            'id'                 => $company->id,
            'name'               => $company->name,
            'cnpj'               => $company->cnpj,
            'email'              => $company->email,
            'phone'              => $company->phone,
            'address_street'      => $company->address_street,
            'address_number'      => $company->address_number,
            'address_complement'  => $company->address_complement,
            'address_neighborhood' => $company->address_neighborhood,
            'address_city'        => $company->address_city,
            'address_state'       => $company->address_state,
            'address_zip_code'    => $company->address_zip_code,
            'status'              => $company->status,
            'created_at'          => $company->created_at?->toDateTimeString(),
            'updated_at'          => $company->updated_at?->toDateTimeString(),
        ]);
    }
}
