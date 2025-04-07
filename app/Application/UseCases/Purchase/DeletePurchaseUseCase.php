<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Domain\Repositories\PurchaseRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeletePurchaseUseCase
{
    public function __construct(
        protected PurchaseRepositoryInterface $purchaseRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->purchaseRepository->delete($id));
    }
}
