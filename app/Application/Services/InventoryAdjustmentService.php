<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\AdjustInventoryDTO;
use App\Application\DTOs\InventoryAdjustmentResultDTO;
use App\Application\DTOs\StockTransactionDTO;
use App\Domain\Enums\InventoryAdjustmentStatus;
use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Enums\StockTransactionReferenceType;
use App\Domain\Exceptions\ZeroDeltaException;
use App\Domain\Models\Stock;
use App\Domain\Repositories\InventoryAdjustmentRepositoryInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use App\Domain\Repositories\StockTransactionRepositoryInterface;

final readonly class InventoryAdjustmentService
{
    public function __construct(
        private StockRepositoryInterface $stockRepository,
        private InventoryAdjustmentRepositoryInterface $adjustmentRepository,
        private StockTransactionRepositoryInterface $transactionRepository,
    ) {
    }

    /**
     * @throws ZeroDeltaException
     */
    public function validateDelta(float $currentQuantity, float $newQuantity): void
    {
        if (round($newQuantity - $currentQuantity, 4) === 0.0) {
            throw new ZeroDeltaException();
        }
    }

    public function apply(
        Stock $stock,
        AdjustInventoryDTO $dto,
    ): InventoryAdjustmentResultDTO {
        $previousQuantity = (float) $stock->current_quantity;
        $newQuantity      = $dto->newPhysicalQuantity;
        $delta            = round($newQuantity - $previousQuantity, 4);
        $unitPrice        = (float) $stock->unit_price;
        $absoluteDelta    = abs($delta);
        $direction        = $delta > 0
            ? StockTransactionDirection::IN
            : StockTransactionDirection::OUT;

        $adjustment = $this->adjustmentRepository->create([
            'stock_id'          => $stock->id,
            'company_id'        => $stock->company_id,
            'user_id'           => $dto->userId,
            'previous_quantity' => $previousQuantity,
            'new_quantity'      => $newQuantity,
            'adjusted_quantity' => $delta,
            'unit'              => $stock->unit,
            'unit_price'        => $unitPrice,
            'status'            => InventoryAdjustmentStatus::PENDING->value,
            'reason'            => $dto->reason,
        ]);

        $transaction = $this->transactionRepository->create(new StockTransactionDTO(
            companyId:     $stock->company_id,
            supplyId:      $stock->supply_id,
            quantity:      $absoluteDelta,
            unitPrice:     $unitPrice,
            totalCost:     round($absoluteDelta * $unitPrice, 2),
            unit:          $stock->unit,
            direction:     $direction,
            referenceId:   $adjustment->id,
            referenceType: StockTransactionReferenceType::ADJUSTMENT,
        ));

        $this->adjustmentRepository->linkTransaction($adjustment, $transaction->id);
        $this->adjustmentRepository->markAsApplied($adjustment);

        $updatedStock = $this->stockRepository->update($stock->id, [
            'current_quantity' => $newQuantity,
        ]);

        return new InventoryAdjustmentResultDTO(
            adjustment: $adjustment->refresh(),
            stock:      $updatedStock->load(['supply', 'supplier']),
            delta:      $delta,
        );
    }
}
