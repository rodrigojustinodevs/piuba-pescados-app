<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Application\DTOs\MortalityDTO;
use App\Domain\Models\Mortality;
use App\Domain\Repositories\MortalityRepositoryInterface;
use App\Domain\Services\Mortality\MortalityService;
use App\Domain\Services\Mortality\MortalityValidatorService;
use App\Infrastructure\Mappers\MortalityMapper;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateMortalityUseCase
{
    public function __construct(
        protected MortalityRepositoryInterface $mortalityRepository,
        protected MortalityValidatorService $mortalityValidator,
        protected MortalityService $mortalityService
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): MortalityDTO
    {
        return DB::transaction(function () use ($id, $data): MortalityDTO {
            $mappedData = MortalityMapper::fromRequest($data);

            $mortality = $this->mortalityRepository->showMortality('id', $id);

            if (! $mortality instanceof Mortality) {
                throw new RuntimeException('Mortality not found');
            }

            $this->mortalityValidator->validate(
                $mortality->batch,
                (int) $mappedData['quantity'],
                $id
            );

            $this->mortalityRepository->update($id, [
                'quantity'       => $mappedData['quantity'],
                'mortality_date' => $mappedData['mortality_date'],
                'cause'          => $data['cause'] ?? $mortality->cause,
            ]);

            $this->mortalityService->checkAndDispatchIfCritical($mortality->batch);

            return MortalityMapper::toDTO($mortality);
        });
    }
}
