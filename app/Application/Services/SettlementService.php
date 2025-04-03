<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\SettlementDTO;
use App\Application\UseCases\Settlement\CreateSettlementUseCase;
use App\Application\UseCases\Settlement\DeleteSettlementUseCase;
use App\Application\UseCases\Settlement\ListSettlementsUseCase;
use App\Application\UseCases\Settlement\ShowSettlementUseCase;
use App\Application\UseCases\Settlement\UpdateSettlementUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SettlementService
{
    public function __construct(
        protected CreateSettlementUseCase $createSettlementUseCase,
        protected ListSettlementsUseCase $listSettlementsUseCase,
        protected ShowSettlementUseCase $showSettlementUseCase,
        protected UpdateSettlementUseCase $updateSettlementUseCase,
        protected DeleteSettlementUseCase $deleteSettlementUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): SettlementDTO
    {
        return $this->createSettlementUseCase->execute($data);
    }

    public function showAllSettlements(): AnonymousResourceCollection
    {
        return $this->listSettlementsUseCase->execute();
    }

    public function showSettlement(string $id): ?SettlementDTO
    {
        return $this->showSettlementUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateSettlement(string $id, array $data): SettlementDTO
    {
        return $this->updateSettlementUseCase->execute($id, $data);
    }

    public function deleteSettlement(string $id): bool
    {
        return $this->deleteSettlementUseCase->execute($id);
    }
}
