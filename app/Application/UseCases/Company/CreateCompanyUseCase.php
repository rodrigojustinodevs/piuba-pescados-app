<?php

declare(strict_types=1);

namespace App\Application\UseCases\Company;

use App\Application\DTOs\CompanyInputDTO;
use App\Domain\Models\Company;
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
    public function execute(array $data): Company
    {
        return DB::transaction(function () use ($data): Company {
            $dto = CompanyInputDTO::fromArray($data);

            return $this->companyRepository->create($dto->toPersistence());
        });
    }
}
