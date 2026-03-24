<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialCategory;

use App\Domain\Models\FinancialCategory;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;

final readonly class DeactivateFinancialCategoryUseCase
{
    public function __construct(
        private FinancialCategoryRepositoryInterface $repository,
    ) {
    }

    /**
     * Inactivates a category that has linked transactions, preserving history.
     */
    public function execute(string $id): FinancialCategory
    {
        $category = $this->repository->findOrFail($id);

        $category->deactivate();

        return $category->refresh()->load('company:id,name');
    }
}
