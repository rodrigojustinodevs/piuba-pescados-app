<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Application\DTOs\StockingInputDTO;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateStockingUseCase
{
    public function __construct(
        private StockingRepositoryInterface $stockingRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): Stocking
    {
        $stocking = $this->stockingRepository->findOrFail($id);
        $dto      = StockingInputDTO::fromArray($data);

        return DB::transaction(fn(): Stocking => $this->stockingRepository->update($stocking->id, [
            'batch_id'       => $dto->batchId,
            'stocking_date'  => $dto->stockingDate,
            'quantity'       => $dto->quantity,
            'average_weight' => $dto->averageWeight,
        ]));
    }
}
