<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Application\DTOs\RegisterStockMovementDTO;
use App\Application\DTOs\TransferStockDTO;
use App\Domain\Enums\StockMovementTypeEnum;
use App\Domain\Exceptions\StockMovementException;
use App\Domain\Models\StockMovement;
use App\Domain\Repositories\StockBalanceRepositoryInterface;
use App\Domain\Repositories\StockMovementRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TransferStockUseCase
{
    public function __construct(
        private readonly StockBalanceRepositoryInterface $balanceRepository,
        private readonly StockMovementRepositoryInterface $movementRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @return array{origin: StockMovement, destination: StockMovement}
     */
    public function execute(array $data): array
    {
        $userId = (string) auth()->id();
        $dto    = TransferStockDTO::fromArray($data, $userId);

        if ($dto->sourceStockId === $dto->destinationStockId) {
            throw StockMovementException::sameStock();
        }

        return DB::transaction(function () use ($dto): array {
            $this->balanceRepository->decrementQuantity($dto->sourceStockId, $dto->supplyId, $dto->quantity);
            $this->balanceRepository->incrementQuantity($dto->destinationStockId, $dto->supplyId, $dto->quantity);

            $origin = $this->movementRepository->create(new RegisterStockMovementDTO(
                stockId:  $dto->sourceStockId,
                supplyId: $dto->supplyId,
                userId:   $dto->userId,
                type:     StockMovementTypeEnum::TRANSFER,
                quantity: $dto->quantity,
                reason:   $dto->reason,
            ));

            $destination = $this->movementRepository->create(new RegisterStockMovementDTO(
                stockId:  $dto->destinationStockId,
                supplyId: $dto->supplyId,
                userId:   $dto->userId,
                type:     StockMovementTypeEnum::TRANSFER,
                quantity: $dto->quantity,
                reason:   $dto->reason,
            ));

            return ['origin' => $origin, 'destination' => $destination];
        });
    }
}
