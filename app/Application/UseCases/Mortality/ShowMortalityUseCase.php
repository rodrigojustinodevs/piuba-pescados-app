<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Domain\Models\Mortality;
use App\Domain\Repositories\MortalityRepositoryInterface;

final readonly class ShowMortalityUseCase
{
    public function __construct(
        private MortalityRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): Mortality
    {
        return $this->repository->findOrFail($id);
    }
}
