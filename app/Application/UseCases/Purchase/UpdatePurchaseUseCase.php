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

        return new PurchaseDTO(
            id: $purchase->id,
            inputName: $purchase->input_name,
            quantity: $purchase->quantity,
            totalPrice: $purchase->total_price,
            supplier: [
                'id'   => $purchase->supplier->id ?? '',
                'name' => $purchase->supplier->name ?? '',
            ],
            company: [
                'name' => $purchase->company->name ?? '',
            ],
            purchaseDate: $purchaseDate->toDateString(),
            createdAt: $purchase->created_at?->toDateTimeString(),
            updatedAt: $purchase->updated_at?->toDateTimeString()
        );
    }
}
