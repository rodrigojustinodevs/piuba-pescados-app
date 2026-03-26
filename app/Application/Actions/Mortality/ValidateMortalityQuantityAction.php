<?php

declare(strict_types=1);

namespace App\Application\Actions\Mortality;

use App\Domain\Exceptions\MortalityExceedsSurvivorsException;
use App\Domain\Models\Batch;
use App\Domain\Repositories\MortalityRepositoryInterface;

final readonly class ValidateMortalityQuantityAction
{
    public function __construct(
        private MortalityRepositoryInterface $mortalityRepository,
    ) {
    }

    public function execute(Batch $batch, int $newQuantity, ?string $excludeMortalityId = null): void
    {
        $otherMortalities = $this->mortalityRepository->totalMortalities(
            (string) $batch->id,
            $excludeMortalityId,
        );

        $currentSurvivors = $batch->initial_quantity - $otherMortalities;

        if ($newQuantity > $currentSurvivors) {
            throw new MortalityExceedsSurvivorsException($currentSurvivors, $newQuantity);
        }
    }
}
