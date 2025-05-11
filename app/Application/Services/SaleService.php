<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\SaleDTO;
use App\Application\UseCases\Sale\CreateSaleUseCase;
use App\Application\UseCases\Sale\DeleteSaleUseCase;
use App\Application\UseCases\Sale\ListSalesUseCase;
use App\Application\UseCases\Sale\ShowSaleUseCase;
use App\Application\UseCases\Sale\UpdateSaleUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SaleService
{
    public function __construct(
        protected CreateSaleUseCase $createSaleUseCase,
        protected ListSalesUseCase $listSalesUseCase,
        protected ShowSaleUseCase $showSaleUseCase,
        protected UpdateSaleUseCase $updateSaleUseCase,
        protected DeleteSaleUseCase $deleteSaleUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): SaleDTO
    {
        return $this->createSaleUseCase->execute($data);
    }

    public function showAllSales(): AnonymousResourceCollection
    {
        return $this->listSalesUseCase->execute();
    }

    public function showSale(string $id): ?SaleDTO
    {
        return $this->showSaleUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateSale(string $id, array $data): SaleDTO
    {
        return $this->updateSaleUseCase->execute($id, $data);
    }

    public function deleteSale(string $id): bool
    {
        return $this->deleteSaleUseCase->execute($id);
    }
}
