<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Domain\Models\Stock;
use App\Domain\Repositories\StockRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateStockUseCase
{
    public function __construct(
        private StockRepositoryInterface $repository,
    ) {
    }

    /**
     * Update only the configurable attributes of the stock (minimum_stock, withdrawal_quantity, unit, unit_price).
     * The current_quantity is managed exclusively via transactions.
     *
     * @param array<string, mixed> $data Validated data from the FormRequest
     */
    public function execute(string $id, array $data): Stock
    {
        return DB::transaction(function () use ($id, $data): Stock {
            $updated = $this->repository->update($id, [
                'minimum_stock'       => $data['minimum_stock'] ?? $data['minimumStock'] ?? null,
                'withdrawal_quantity' => $data['withdrawal_quantity'] ?? $data['withdrawalQuantity'] ?? null,
                'unit'                => $data['unit'] ?? null,
                'unit_price'          => $data['unit_price'] ?? $data['unitPrice'] ?? null,
            ]);

            return $updated->loadMissing(['supply', 'supplier']);
        });
    }
}
