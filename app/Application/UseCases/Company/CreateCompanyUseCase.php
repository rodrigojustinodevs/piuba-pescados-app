<?php

declare(strict_types=1);

namespace App\Application\UseCases\Company;

use App\Application\DTOs\CompanyDTO;
use App\Domain\Repositories\CompanyRepositoryInterface;
use App\Infrastructure\Mappers\CompanyMapper;
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
            // Usar Mapper para converter request em formato de persistência
            // O Mapper encapsula criação de Value Objects e validações
            $mappedData = CompanyMapper::fromRequest($data);

            // Criar entidade através do Repository
            $company = $this->companyRepository->create($mappedData);

            // Usar Mapper para converter Model em DTO
            return CompanyMapper::toDTO($company);
        });
    }
}
