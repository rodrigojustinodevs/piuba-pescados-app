<?php

declare(strict_types=1);

namespace App\Application\UseCases\Company;

use App\Domain\Models\Company;
use App\Domain\Repositories\CompanyRepositoryInterface;

class ShowCompanyUseCase
{
    public function __construct(
        protected CompanyRepositoryInterface $companyRepository
    ) {
    }

    public function execute(string $id): ?Company
    {
        return $this->companyRepository->showCompany('id', $id);
    }
}
