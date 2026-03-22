<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Application\DTOs\StockSettingsDTO;
use App\Domain\Models\Stock;
use App\Domain\Repositories\StockRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class UpdateStockSettingsUseCase
{
    public function __construct(
        private readonly StockRepositoryInterface $repository,
    ) {}

    /**
     * Update only the configurable attributes of the stock.
     * The current_quantity and unit_price are managed exclusively via transactions.
     *
     * @param array<string, mixed> $data Validated data from the FormRequest
     */
    public function execute(string $id, array $data): Stock
    {
        $dto = StockSettingsDTO::fromArray($data);

        return DB::transaction(function () use ($id, $dto): Stock {
            return $this->repository
                ->update($id, $dto->toPersistence())
                ->loadMissing(['supply', 'supplier']);
        });
    }
}