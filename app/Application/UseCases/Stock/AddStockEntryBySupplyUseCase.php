<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Application\Actions\Stock\RegisterStockTransactionAction;
use App\Application\DTOs\StockInputDTO;
use App\Application\DTOs\StockTransactionDTO;
use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Enums\StockTransactionReferenceType;
use App\Domain\Models\Stock;
use App\Domain\Repositories\StockRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class AddStockEntryBySupplyUseCase
{
    public function __construct(
        private StockRepositoryInterface $repository,
        private RegisterStockTransactionAction $registerTransaction,
    ) {
    }

    /**
     * Add entry to the stock of a specific supply.
     * If the stock does not exist for the supply, create a new record.
     * Called by ApplyPurchaseToStockAction after receiving a purchase.
     */
    public function execute(StockInputDTO $dto): Stock
    {
        return DB::transaction(function () use ($dto): Stock {
            $stock = $this->repository->findBySupply($dto->companyId, $dto->supplyId);

            if (!$stock instanceof \App\Domain\Models\Stock) {
                $stock = $this->repository->create($dto);
            } else {
                $stock = $this->repository->incrementQuantity($stock->id, $dto->quantity);

                $stock = $this->repository->update($stock->id, [
                    'unit_price' => $dto->unitPrice,
                ]);
            }

            $this->registerTransaction->execute(new StockTransactionDTO(
                companyId:     $dto->companyId,
                supplyId:      $dto->supplyId,
                quantity:      $dto->quantity,
                unitPrice:     $dto->unitPrice,
                totalCost:     $dto->totalCost,
                unit:          $dto->unit,
                direction:     StockTransactionDirection::IN,
                referenceId:   $dto->referenceId,
                referenceType: StockTransactionReferenceType::PURCHASE_ITEM,
            ));

            return $stock->load(['supply', 'supplier']);
        });
    }
}
