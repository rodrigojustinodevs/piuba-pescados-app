<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Application\Contracts\UserResolverInterface;
use App\Application\DTOs\AdjustInventoryDTO;
use App\Application\DTOs\InventoryAdjustmentResultDTO;
use App\Application\Services\InventoryAdjustmentService;
use App\Domain\Repositories\StockRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Adjusts the stock quantity for a given stock ID.
 */
final class AdjustStockUseCase
{
    public function __construct(
        private readonly StockRepositoryInterface   $repository,
        private readonly InventoryAdjustmentService $adjustmentService,
        private readonly UserResolverInterface       $userResolver,
    ) {}

    /**
     * @param array<string, mixed> $data Dados validados pelo StockAdjustRequest
     *                                   Deve conter: new_physical_quantity, user_id, reason?
     */
    public function execute(string $id, array $data): InventoryAdjustmentResultDTO
    {
        $userId = $this->userResolver->resolve(
            hint: $data['user_id'] ?? $data['userId'] ?? null,
        );
        $dto = new AdjustInventoryDTO(
            stockId:             $id,
            newPhysicalQuantity: (float)  ($data['new_physical_quantity'] ?? $data['physicalQuantity'] ?? $data['physical_quantity']),
            userId:              $userId,
            reason:              $data['reason'] ?? null,
        );

        $stock = $this->repository->findOrFail($dto->stockId);

        $this->adjustmentService->validateDelta(
            currentQuantity: (float) $stock->current_quantity,
            newQuantity:     $dto->newPhysicalQuantity,
        );

        return DB::transaction(
            fn (): InventoryAdjustmentResultDTO => $this->adjustmentService->apply($stock, $dto)
        );
    }
}