<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Application\Actions\ApplyPurchaseToStockAction;
use App\Domain\Enums\PurchaseStatus;
use App\Domain\Exceptions\InvalidPurchaseStatusTransitionException;
use App\Domain\Models\Purchase;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class ReceivePurchaseUseCase
{
    public function __construct(
        private PurchaseRepositoryInterface $repository,
        private ApplyPurchaseToStockAction $applyToStock,
    ) {
    }

    public function execute(string $id): Purchase
    {
        $purchase      = $this->repository->findOrFail($id);
        $currentStatus = PurchaseStatus::from($purchase->status);

        if (! $currentStatus->canTransitionTo(PurchaseStatus::RECEIVED)) {
            throw new InvalidPurchaseStatusTransitionException(
                from: $currentStatus,
                to:   PurchaseStatus::RECEIVED,
            );
        }

        return DB::transaction(function () use ($purchase): Purchase {
            $updated = $this->repository->update($purchase->id, [
                'status'      => PurchaseStatus::RECEIVED->value,
                'received_at' => now()->toDateTimeString(),
            ]);

            $this->applyToStock->execute($updated->load('items'));

            return $updated;
        });
    }
}
