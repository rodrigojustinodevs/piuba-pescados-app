<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Application\DTOs\MortalityDTO;
use App\Domain\Models\Batch;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\MortalityRepositoryInterface;
use App\Domain\Services\Mortality\MortalityService;
use App\Domain\Services\Mortality\MortalityValidatorService;
use App\Infrastructure\Mappers\MortalityMapper;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateMortalityUseCase
{
    public function __construct(
        private readonly MortalityRepositoryInterface $mortalityRepository,
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly MortalityValidatorService $validatorService,
        private readonly MortalityService $mortalityService
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): MortalityDTO
    {
        return DB::transaction(function () use ($data): MortalityDTO {
            $mappedData = MortalityMapper::fromRequest($data);

            $batch = $this->batchRepository->showBatch('id', $mappedData['batch_id']);

            if (! $batch instanceof Batch) {
                throw new RuntimeException('Batch not found');
            }

            $this->validatorService->validate($batch, (int) $mappedData['quantity']);

            $mortality = $this->mortalityRepository->create($mappedData);

            $this->mortalityService->checkAndDispatchIfCritical($batch);

            return MortalityMapper::toDTO($mortality);
        });
    }
}
