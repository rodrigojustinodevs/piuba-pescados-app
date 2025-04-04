<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Application\DTOs\MortalityDTO;
use App\Domain\Models\Mortality;
use App\Domain\Repositories\MortalityRepositoryInterface;
use RuntimeException;

class ShowMortalityUseCase
{
    public function __construct(
        protected MortalityRepositoryInterface $mortalityRepository
    ) {
    }

    public function execute(string $id): ?MortalityDTO
    {
        $mortality = $this->mortalityRepository->showMortality('id', $id);

        if (! $mortality instanceof Mortality) {
            throw new RuntimeException('Mortality not found');
        }

        return new MortalityDTO(
            id: $mortality->id,
            batcheId: $mortality->batche_id,
            quantity: $mortality->quantity,
            cause: $mortality->cause,
            createdAt: $mortality->created_at?->toDateTimeString(),
            updatedAt: $mortality->updated_at?->toDateTimeString()
        );
    }
}
