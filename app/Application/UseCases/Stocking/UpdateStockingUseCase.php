<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Application\DTOs\StockingDTO;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\StockingRepositoryInterface;
use App\Infrastructure\Mappers\StockingMapper;
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
    public function execute(string $id, array $data): StockingDTO
    {
        return DB::transaction(function () use ($id, $data): StockingDTO {
            $mappedData = StockingMapper::fromRequest($data);
            $stocking = $this->stockingRepository->update($id, $mappedData);

            if (! $stocking instanceof Stocking) {
                throw new RuntimeException('Stocking not found');
            }

            return StockingMapper::toDTO($stocking);
        });
    }
}
