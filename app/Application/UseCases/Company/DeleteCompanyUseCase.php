<?php

declare(strict_types=1);

namespace App\Application\UseCases\Company;

use App\Domain\Repositories\CompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteCompanyUseCase
{
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
    ) {
    }

    public function execute(string $id): void
    {
        $this->companyRepository->findOrFail($id);

        DB::transaction(function () use ($id): void {
            $this->companyRepository->delete($id);
        });
    }
}
