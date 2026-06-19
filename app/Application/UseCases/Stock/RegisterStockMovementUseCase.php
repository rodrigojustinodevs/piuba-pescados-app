<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Application\DTOs\RegisterStockMovementDTO;
use App\Domain\Enums\StockMovementTypeEnum;
use App\Domain\Models\StockMovement;
use App\Domain\Repositories\StockBalanceRepositoryInterface;
use App\Domain\Repositories\StockMovementRepositoryInterface;
use Illuminate\Support\Facades\DB;

class RegisterStockMovementUseCase
{
    public function __construct(
        private readonly StockBalanceRepositoryInterface $balanceRepository,
        private readonly StockMovementRepositoryInterface $movementRepository,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function execute(array $data): StockMovement
    {
        $userId = (string) auth()->id();
        $dto    = RegisterStockMovementDTO::fromArray($data, $userId);

        return DB::transaction(function () use ($dto): StockMovement {
            match ($dto->type) {
                StockMovementTypeEnum::ENTRY => $this->balanceRepository->incrementQuantity(
                    $dto->stockId,
                    $dto->supplyId,
                    $dto->quantity
                ),

                StockMovementTypeEnum::EXIT => $this->balanceRepository->decrementQuantity(
                    $dto->stockId,
                    $dto->supplyId,
                    $dto->quantity
                ),

                StockMovementTypeEnum::ADJUSTMENT => $this->balanceRepository->setQuantity(
                    $dto->stockId,
                    $dto->supplyId,
                    $dto->quantity
                ),

                StockMovementTypeEnum::TRANSFER => $this->balanceRepository->incrementQuantity(
                    $dto->stockId,
                    $dto->supplyId,
                    $dto->quantity
                ),
            };

            return $this->movementRepository->create($dto);
        });
    }
}
