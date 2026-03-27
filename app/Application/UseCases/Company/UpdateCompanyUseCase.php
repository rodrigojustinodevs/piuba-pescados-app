<?php

declare(strict_types=1);

namespace App\Application\UseCases\Company;

use App\Application\DTOs\CompanyInputDTO;
use App\Domain\Models\Company;
use App\Domain\Repositories\CompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateCompanyUseCase
{
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): Company
    {
        $company = $this->companyRepository->findOrFail($id);
        $dto     = CompanyInputDTO::fromArray($data);

        return DB::transaction(function () use ($company, $dto): Company {
            $updated = $this->companyRepository->update($company->id, $dto->toPersistence());
            return $updated->refresh();
        });
    }
}
