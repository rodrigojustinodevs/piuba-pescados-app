<?php

declare(strict_types=1);

namespace App\Application\UseCases\Company;

use App\Application\DTOs\CompanyDTO;
use App\Domain\Models\Company;
use App\Domain\Repositories\CompanyRepositoryInterface;
use App\Infrastructure\Mappers\CompanyMapper;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateCompanyUseCase
{
    public function __construct(
        protected CompanyRepositoryInterface $companyRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): CompanyDTO
    {
        return DB::transaction(function () use ($id, $data): CompanyDTO {
            // Usar Mapper para converter request em formato de persistência
            // O Mapper encapsula criação de Value Objects e validações
            $mappedData = CompanyMapper::fromRequest($data);

            $company = $this->companyRepository->update($id, $mappedData);

            if (! $company instanceof Company) {
                throw new RuntimeException('Company not found');
            }

            // Usar Mapper para converter Model em DTO
            return CompanyMapper::toDTO($company);
        });
    }
}
