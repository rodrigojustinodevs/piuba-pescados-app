<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Application\DTOs\PurchaseDTO;
use App\Domain\Models\Purchase;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use Carbon\Carbon;
use RuntimeException;

class UpdatePurchaseUseCase
{
    public function __construct(
        protected PurchaseRepositoryInterface $purchaseRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): PurchaseDTO
    {
        $purchase = $this->purchaseRepository->update($id, $data);

        if (! $purchase instanceof Purchase) {
            throw new RuntimeException('Purchase not found');
        }

        $purchaseDate = $purchase->purchase_date instanceof Carbon
            ? $purchase->purchase_date
            : Carbon::parse($purchase->purchase_date);

        $stocking = $purchase->stocking;    

        return new PurchaseDTO(
            id: $purchase->id,
            itemName: $purchase->item_name,
            quantity: $purchase->quantity,
            totalPrice: $purchase->total_price,
            purchaseDate: $purchaseDate->toDateString(),
            supplier: [
                'id'   => $purchase->supplier->id ?? '',
                'name' => $purchase->supplier->name ?? '',
            ],
            company: [
                'name' => $purchase->company->name ?? '',
            ],
            stockingId: $purchase->stocking_id,
            stocking: $stocking ? [
                'id'           => $stocking->id,
                'stockingDate' => $stocking->stocking_date?->toDateString(),
            ] : null,
            createdAt: $purchase->created_at?->toDateTimeString(),
            updatedAt: $purchase->updated_at?->toDateTimeString()
        );
    }
}
