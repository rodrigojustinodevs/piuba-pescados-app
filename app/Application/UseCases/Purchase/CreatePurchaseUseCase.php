<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Application\DTOs\PurchaseDTO;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreatePurchaseUseCase
{
    public function __construct(
        protected PurchaseRepositoryInterface $purchaseRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): PurchaseDTO
    {
        return DB::transaction(function () use ($data): PurchaseDTO {
            $purchase = $this->purchaseRepository->create($data);

            $purchaseDate = $purchase->purchase_date instanceof Carbon
                ? $purchase->purchase_date
                : Carbon::parse($purchase->purchase_date);

            return new PurchaseDTO(
                id: $purchase->id,
                inputName: $purchase->input_name,
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
                createdAt: $purchase->created_at?->toDateTimeString(),
                updatedAt: $purchase->updated_at?->toDateTimeString()
            );
        });
    }
}
