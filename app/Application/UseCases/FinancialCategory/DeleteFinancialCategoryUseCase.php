<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialCategory;

use App\Domain\Repositories\FinancialCategoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteFinancialCategoryUseCase
{
    public function __construct(
        protected FinancialCategoryRepositoryInterface $financialCategoryRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->financialCategoryRepository->delete($id));
    }
}
