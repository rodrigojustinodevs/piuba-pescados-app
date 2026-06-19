<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Application\Actions\Purchase\ApplyPurchaseToStockAction;
use App\Application\DTOs\ReceivePurchaseDTO;
use App\Domain\Enums\PurchaseStatus;
use App\Domain\Exceptions\PurchaseReceivingException;
use App\Domain\Models\Purchase;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class ReceivePurchaseUseCase
{
    public function __construct(
        private PurchaseRepositoryInterface $repository,
        private ApplyPurchaseToStockAction $applyToStockAction,
    ) {
    }

    public function execute(ReceivePurchaseDTO $dto): Purchase
    {
        return DB::transaction(function () use ($dto): Purchase {
            $purchase = $this->repository->findOrFail($dto->purchaseId);

            if ($purchase->status === PurchaseStatus::CANCELLED) {
                throw PurchaseReceivingException::cancelled();
            }

            if ($purchase->status === PurchaseStatus::RECEIVED) {
                throw PurchaseReceivingException::alreadyReceived();
            }

            $purchase->load('items');
            $itemsById = $purchase->items->keyBy('id');

            foreach ($dto->items as $itemData) {
                $itemId         = (string) $itemData['purchase_item_id'];
                $receivedAmount = (float) $itemData['received_quantity'];

                $purchaseItem = $itemsById->get($itemId);

                if ($purchaseItem === null) {
                    throw PurchaseReceivingException::itemNotBelongsToPurchase($itemId);
                }

                if (! $purchaseItem->canReceive($receivedAmount)) {
                    throw PurchaseReceivingException::quantityExceedsPending(
                        $receivedAmount,
                        $purchaseItem->getPendingQuantity(),
                    );
                }

                $this->applyToStockAction->executeForItem(
                    purchase: $purchase,
                    item:     $purchaseItem,
                    quantity: $receivedAmount,
                );

                $purchaseItem->receive($receivedAmount);
            }

            $purchase->refresh()->load('items');
            $purchase->updateReceivingStatus();

            return $purchase->refresh()->load(['supplier', 'company', 'items.supply']);
        });
    }
}
