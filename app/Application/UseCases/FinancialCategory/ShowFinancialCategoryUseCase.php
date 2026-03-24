<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialCategory;

use App\Domain\Models\FinancialCategory;
use App\Domain\Repositories\FinancialCategoryRepositoryInterface;

final readonly class ShowFinancialCategoryUseCase
{
    public function __construct(
        private FinancialCategoryRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): FinancialCategory
    {
        return $this->repository->findOrFail($id);
    }
}
