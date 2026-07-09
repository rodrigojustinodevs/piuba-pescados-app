<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\SaleItemDTO;
use App\Domain\Models\SaleItem;
use Illuminate\Support\Collection;

interface SaleItemRepositoryInterface
{
    public function create(string $saleId, SaleItemDTO $dto): SaleItem;

    /**
     * @return Collection<int, SaleItem>
     */
    public function findBySale(string $saleId): Collection;

    public function updateCosts(string $saleItemId, float $unitCost, float $totalCost): void;

    public function syncFirstItemWeightAndPrice(string $saleId, float $totalWeight, float $pricePerKg): void;
}
