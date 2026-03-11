<?php

declare(strict_types=1);

namespace App\Domain\Services\Mortality;

use App\Domain\Models\Batch;
use App\Domain\Repositories\MortalityRepositoryInterface;
use Illuminate\Validation\ValidationException;

class MortalityValidatorService
{
    public function __construct(
        private readonly MortalityRepositoryInterface $mortalityRepository,
    ) {
    }

    public function validate(Batch $batch, int $newQuantity, ?string $excludeMortalityId = null): void
    {
        $otherMortalities = $this->mortalityRepository->totalMortalities($batch->id, $excludeMortalityId);

        $currentSurvivors = $batch->initial_quantity - $otherMortalities;

        if ($newQuantity > $currentSurvivors) {
            throw ValidationException::withMessages([
                'quantity' => "Operação inválida. O lote possui apenas {$currentSurvivors} peixes vivos, " .
                "mas você tentou registrar {$newQuantity} mortes.",
            ]);
        }
    }
}
