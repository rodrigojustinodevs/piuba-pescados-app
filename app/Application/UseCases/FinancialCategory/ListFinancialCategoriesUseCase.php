<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialCategory;

use App\Domain\Repositories\FinancialCategoryRepositoryInterface;
use App\Domain\Repositories\PaginationInterface;

final readonly class ListFinancialCategoriesUseCase
{
    public function __construct(
        private FinancialCategoryRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        return $this->repository->paginate($filters);
    }
}
