<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Application\DTOs\StockingInputDTO;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateStockingUseCase
{
    public function __construct(
        protected StockingRepositoryInterface $stockingRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): Stocking
    {
        return DB::transaction(function () use ($id, $data): Stocking {
            $dto      = StockingInputDTO::fromArray($data);
            $stocking = $this->stockingRepository->update($id, $dto->toPersistence());

            if (! $stocking instanceof Stocking) {
                throw new RuntimeException('Stocking not found');
            }

            return $stocking;
        });
    }
}
