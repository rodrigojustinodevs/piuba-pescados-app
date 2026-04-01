<?php

declare(strict_types=1);

namespace App\Application\UseCases\Company;

use App\Domain\Repositories\CompanyRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final readonly class ShowAllCompaniesUseCase
{
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        return $this->companyRepository->paginate($filters);
    }
}
