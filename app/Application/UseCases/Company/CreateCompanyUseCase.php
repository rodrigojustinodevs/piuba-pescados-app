<?php

declare(strict_types=1);

namespace App\Application\UseCases\Company;

use App\Application\DTOs\CompanyInputDTO;
use App\Domain\Models\Company;
use App\Domain\Repositories\CompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateCompanyUseCase
{
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Company
    {
        $dto = CompanyInputDTO::fromArray($data);

        return DB::transaction(fn (): Company => $this->companyRepository->create($dto));
    }
}
