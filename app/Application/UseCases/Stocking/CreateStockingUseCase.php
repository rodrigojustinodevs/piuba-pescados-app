<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stocking;

use App\Application\DTOs\StockingInputDTO;
use App\Domain\Models\Stocking;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateStockingUseCase
{
    public function __construct(
        private StockingRepositoryInterface $stockingRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Stocking
    {
        return DB::transaction(function () use ($data): Stocking {
            $dto = StockingInputDTO::fromArray($data);

            return $this->stockingRepository->create($dto);
        });
    }
}
