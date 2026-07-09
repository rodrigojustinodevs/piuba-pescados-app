<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Domain\Models\Purchase;
use App\Domain\Repositories\PurchaseRepositoryInterface;

final readonly class GetPurchasePaymentsUseCase
{
    public function __construct(
        private PurchaseRepositoryInterface $repository,
    ) {
    }

    public function execute(string $purchaseId): Purchase
    {
        $purchase = $this->repository->findOrFail($purchaseId);

        return $purchase->load([
            'payments' => static fn ($q) => $q->orderBy('payment_date', 'desc'),
        ]);
    }
}
