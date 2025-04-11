<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialCategory;

use App\Application\DTOs\FinancialCategoryDTO;
use App\Domain\Enums\FinancialCategoryType;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateFinancialCategoryUseCase
{
    public function __construct(
        protected FinancialCategoryRepositoryInterface $financialCategoryRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): FinancialCategoryDTO
    {
        return DB::transaction(function () use ($data): FinancialCategoryDTO {
            $financialCategory = $this->financialCategoryRepository->create($data);

            $createdAt = $financialCategory->created_at instanceof Carbon
                ? $financialCategory->created_at
                : Carbon::parse($financialCategory->created_at);

            return new FinancialCategoryDTO(
                id: $financialCategory->id,
                name: $financialCategory->name,
                type: FinancialCategoryType::from($financialCategory->type),
                company: [
                    'name' => $financialCategory->company->name ?? '',
                ],
                createdAt: $createdAt->toDateTimeString(),
                updatedAt: $financialCategory->updated_at?->toDateTimeString()
            );
        });
    }
}
