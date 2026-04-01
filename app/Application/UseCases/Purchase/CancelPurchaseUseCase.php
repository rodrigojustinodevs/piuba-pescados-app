<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Domain\Enums\PurchaseStatus;
use App\Domain\Exceptions\InvalidPurchaseStatusTransitionException;
use App\Domain\Models\Purchase;
use App\Domain\Repositories\PurchaseRepositoryInterface;

final readonly class CancelPurchaseUseCase
{
    public function __construct(
        private PurchaseRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): Purchase
    {
        $purchase      = $this->repository->findOrFail($id);
        $currentStatus = PurchaseStatus::from($purchase->status);

        if (! $currentStatus->canTransitionTo(PurchaseStatus::CANCELLED)) {
            throw new InvalidPurchaseStatusTransitionException(
                from: $currentStatus,
                to:   PurchaseStatus::CANCELLED,
            );
        }

        return $this->repository->update($purchase->id, [
            'status' => PurchaseStatus::CANCELLED->value,
        ]);
    }
}
