<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Application\DTOs\MortalityInputDTO;
use App\Domain\Events\MortalityRecorded;
use App\Domain\Models\Batch;
use App\Domain\Models\Mortality;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\MortalityRepositoryInterface;
use App\Domain\Services\Mortality\MortalityService;
use App\Domain\Services\Mortality\MortalityValidatorService;
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
    public function execute(array $data): Mortality
    {
        return DB::transaction(function () use ($data): Mortality {
            $dto = MortalityInputDTO::fromArray($data);

            $batch = $this->batchRepository->showBatch('id', $dto->batchId);

            if (! $batch instanceof Batch) {
                throw new RuntimeException('Batch not found');
            }

            $this->validatorService->validate($batch, $dto->quantity);

            $mortality = $this->mortalityRepository->create($dto->toPersistence());

            $companyId = $batch->tank->company_id ?? $batch->tank()->value('company_id');
            MortalityRecorded::dispatch($mortality, (string) $companyId);

            $this->mortalityService->checkAndDispatchIfCritical($batch);

            return $mortality;
        });
    }
}
