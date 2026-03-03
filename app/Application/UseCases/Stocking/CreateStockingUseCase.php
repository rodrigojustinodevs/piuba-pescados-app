<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Application\DTOs\StockingDTO;
use App\Domain\Repositories\StockingRepositoryInterface;
use App\Infrastructure\Mappers\StockingMapper;
use Illuminate\Support\Facades\DB;

class CreateStockingUseCase
{
    public function __construct(
        protected StockingRepositoryInterface $stockingRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): StockingDTO
    {
        return DB::transaction(function () use ($data): StockingDTO {
            $mappedData = StockingMapper::fromRequest($data);
            $stocking = $this->stockingRepository->create($mappedData);

            return StockingMapper::toDTO($stocking);
        });
    }
}
