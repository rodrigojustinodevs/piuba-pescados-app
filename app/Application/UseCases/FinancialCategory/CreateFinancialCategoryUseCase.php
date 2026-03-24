<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialCategory;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\FinancialCategoryInputDTO;
use App\Domain\Models\FinancialCategory;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;

final readonly class CreateFinancialCategoryUseCase
{
    public function __construct(
        private FinancialCategoryRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados já validados pelo FormRequest
     */
    public function execute(array $data): FinancialCategory
    {
        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        $dto = FinancialCategoryInputDTO::fromArray($data);

        return $this->repository->create($dto);
    }
}
