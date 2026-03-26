<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Domain\Models\Mortality;
use App\Domain\Repositories\MortalityRepositoryInterface;
use RuntimeException;

class ShowMortalityUseCase
{
    public function __construct(
        protected MortalityRepositoryInterface $mortalityRepository
    ) {
    }

    public function execute(string $id): Mortality
    {
        $mortality = $this->mortalityRepository->showMortality('id', $id);

        if (! $mortality instanceof Mortality) {
            throw new RuntimeException('Mortality not found');
        }

        return $mortality;
    }
}
