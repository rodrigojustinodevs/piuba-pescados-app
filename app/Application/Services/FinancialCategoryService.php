<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\FinancialCategoryDTO;
use App\Application\UseCases\FinancialCategory\CreateFinancialCategoryUseCase;
use App\Application\UseCases\FinancialCategory\DeleteFinancialCategoryUseCase;
use App\Application\UseCases\FinancialCategory\ListFinancialCategoriesUseCase;
use App\Application\UseCases\FinancialCategory\ShowFinancialCategoryUseCase;
use App\Application\UseCases\FinancialCategory\UpdateFinancialCategoryUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FinancialCategoryService
{
    public function __construct(
        protected CreateFinancialCategoryUseCase $createFinancialCategoryUseCase,
        protected ListFinancialCategoriesUseCase $listFinancialCategoriesUseCase,
        protected ShowFinancialCategoryUseCase $showFinancialCategoryUseCase,
        protected UpdateFinancialCategoryUseCase $updateFinancialCategoryUseCase,
        protected DeleteFinancialCategoryUseCase $deleteFinancialCategoryUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): FinancialCategoryDTO
    {
        return $this->createFinancialCategoryUseCase->execute($data);
    }

    public function showAllFinancialCategories(): AnonymousResourceCollection
    {
        return $this->listFinancialCategoriesUseCase->execute();
    }

    public function showFinancialCategory(string $id): ?FinancialCategoryDTO
    {
        return $this->showFinancialCategoryUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateFinancialCategory(string $id, array $data): FinancialCategoryDTO
    {
        return $this->updateFinancialCategoryUseCase->execute($id, $data);
    }

    public function deleteFinancialCategory(string $id): bool
    {
        return $this->deleteFinancialCategoryUseCase->execute($id);
    }
}
