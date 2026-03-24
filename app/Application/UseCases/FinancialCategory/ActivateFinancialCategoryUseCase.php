<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialCategory;

use App\Domain\Models\FinancialCategory;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;

final readonly class ActivateFinancialCategoryUseCase
{
    public function __construct(
        private FinancialCategoryRepositoryInterface $repository,
    ) {
    }

    /**
     * Reativa uma categoria previamente inativada.
     */
    public function execute(string $id): FinancialCategory
    {
        $category = $this->repository->findOrFail($id);

        $category->activate();

        return $category->refresh()->load('company');
    }
}
