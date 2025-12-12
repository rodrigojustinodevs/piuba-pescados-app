<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\TankDTO;
use App\Application\UseCases\Tank\CreateTankUseCase;
use App\Application\UseCases\Tank\DeleteTankUseCase;
use App\Application\UseCases\Tank\GetTankTypesUseCase;
use App\Application\UseCases\Tank\ShowAllTanksUseCase;
use App\Application\UseCases\Tank\ShowTankUseCase;
use App\Application\UseCases\Tank\UpdateTankUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TankService
{
    public function __construct(
        protected CreateTankUseCase $createTankUseCase,
        protected ShowAllTanksUseCase $showAllTanksUseCase,
        protected ShowTankUseCase $showTankUseCase,
        protected UpdateTankUseCase $updateTankUseCase,
        protected DeleteTankUseCase $deleteTankUseCase,
        protected GetTankTypesUseCase $getTankTypesUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): TankDTO
    {
        return $this->createTankUseCase->execute($data);
    }

    public function showAllTanks(): AnonymousResourceCollection
    {
        return $this->showAllTanksUseCase->execute();
    }

    public function showTank(string $id): ?TankDTO
    {
        return $this->showTankUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateTank(string $id, array $data): TankDTO
    {
        return $this->updateTankUseCase->execute($id, $data);
    }

    public function deleteTank(string $id): bool
    {
        return $this->deleteTankUseCase->execute($id);
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    public function getTankTypes(): array
    {
        return $this->getTankTypesUseCase->execute();
    }
}
