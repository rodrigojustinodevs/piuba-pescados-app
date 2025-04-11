<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialCategory;

use App\Application\DTOs\FinancialCategoryDTO;
use App\Domain\Enums\FinancialCategoryType;
use App\Domain\Models\FinancialCategory;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;
use RuntimeException;

class UpdateFinancialCategoryUseCase
{
    public function __construct(
        protected FinancialCategoryRepositoryInterface $financialCategoryRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): FinancialCategoryDTO
    {
        $financialCategory = $this->financialCategoryRepository->update($id, $data);

        if (! $financialCategory instanceof FinancialCategory) {
            throw new RuntimeException('Financial category not found');
        }

        return new FinancialCategoryDTO(
            id: $financialCategory->id,
            name: $financialCategory->name,
            type: FinancialCategoryType::from($financialCategory->type),
            company: [
                'name' => $financialCategory->company->name ?? '',
            ],
            createdAt: $financialCategory->created_at?->toDateTimeString(),
            updatedAt: $financialCategory->updated_at?->toDateTimeString()
        );
    }
}
