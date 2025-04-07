<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\PurchaseDTO;
use App\Application\UseCases\Purchase\CreatePurchaseUseCase;
use App\Application\UseCases\Purchase\DeletePurchaseUseCase;
use App\Application\UseCases\Purchase\ListPurchasesUseCase;
use App\Application\UseCases\Purchase\ShowPurchaseUseCase;
use App\Application\UseCases\Purchase\UpdatePurchaseUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PurchaseService
{
    public function __construct(
        protected CreatePurchaseUseCase $createPurchaseUseCase,
        protected ListPurchasesUseCase $listPurchasesUseCase,
        protected ShowPurchaseUseCase $showPurchaseUseCase,
        protected UpdatePurchaseUseCase $updatePurchaseUseCase,
        protected DeletePurchaseUseCase $deletePurchaseUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): PurchaseDTO
    {
        return $this->createPurchaseUseCase->execute($data);
    }

    public function showAll(): AnonymousResourceCollection
    {
        return $this->listPurchasesUseCase->execute();
    }

    public function show(string $id): ?PurchaseDTO
    {
        return $this->showPurchaseUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): PurchaseDTO
    {
        return $this->updatePurchaseUseCase->execute($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->deletePurchaseUseCase->execute($id);
    }
}
