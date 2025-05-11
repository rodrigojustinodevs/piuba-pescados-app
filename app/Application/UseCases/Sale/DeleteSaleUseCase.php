<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteSaleUseCase
{
    public function __construct(
        protected SaleRepositoryInterface $saleRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->saleRepository->delete($id));
    }
}
