<?php

declare(strict_types=1);

namespace App\Application\UseCases\Company;

use App\Application\DTOs\CompanyDTO;
use App\Domain\Models\Company;
use App\Domain\Repositories\CompanyRepositoryInterface;
use App\Infrastructure\Mappers\CompanyMapper;

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

        // Usar Mapper para converter Model em DTO
        return CompanyMapper::toDTO($company);
    }
}
