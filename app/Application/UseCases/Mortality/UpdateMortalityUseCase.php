<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Application\DTOs\MortalityDTO;
use App\Domain\Models\Mortality;
use App\Domain\Repositories\MortalityRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateMortalityUseCase
{
    public function __construct(
        protected MortalityRepositoryInterface $mortalityRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): MortalityDTO
    {
        return DB::transaction(function () use ($id, $data): MortalityDTO {
            $mortality = $this->mortalityRepository->update($id, $data);

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
        });
    }
}
