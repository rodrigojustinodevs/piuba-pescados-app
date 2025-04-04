<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\MortalityDTO;
use App\Application\UseCases\Mortality\CreateMortalityUseCase;
use App\Application\UseCases\Mortality\DeleteMortalityUseCase;
use App\Application\UseCases\Mortality\ListMortalitiesUseCase;
use App\Application\UseCases\Mortality\ShowMortalityUseCase;
use App\Application\UseCases\Mortality\UpdateMortalityUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MortalityService
{
    public function __construct(
        protected CreateMortalityUseCase $createMortalityUseCase,
        protected ListMortalitiesUseCase $listMortalitiesUseCase,
        protected ShowMortalityUseCase $showMortalityUseCase,
        protected UpdateMortalityUseCase $updateMortalityUseCase,
        protected DeleteMortalityUseCase $deleteMortalityUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): MortalityDTO
    {
        return $this->createMortalityUseCase->execute($data);
    }

    public function showAllMortalities(): AnonymousResourceCollection
    {
        return $this->listMortalitiesUseCase->execute();
    }

    public function showMortality(string $id): ?MortalityDTO
    {
        return $this->showMortalityUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateMortality(string $id, array $data): MortalityDTO
    {
        return $this->updateMortalityUseCase->execute($id, $data);
    }

    public function deleteMortality(string $id): bool
    {
        return $this->deleteMortalityUseCase->execute($id);
    }
}
