<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Application\DTOs\FeedingInputDTO;
use App\Domain\Models\Feeding;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Services\Alert\AlertService;
use App\Domain\Services\Feeding\FeedingService;
use Illuminate\Support\Facades\DB;

class UpdateFeedingUseCase
{
    public function __construct(
        private readonly FeedingRepositoryInterface $feedingRepository,
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly BiometryRepositoryInterface $biometryRepository,
        private readonly FeedingService $feedingService,
        private readonly AlertService $alertService,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): Feeding
    {
        return DB::transaction(function () use ($id, $data): Feeding {
            $feeding = $this->feedingRepository->showFeeding('id', $id);

            if (! $feeding instanceof Feeding) {
                throw new \RuntimeException('Feeding not found');
            }

            $dto       = FeedingInputDTO::fromArray($data);
            $batch     = $this->batchRepository->showBatch('id', $dto->batchId);
            $companyId = $batch->tank?->company_id;

            $this->feedingService->revertStockEffect($feeding, $companyId);

            $updatedFeeding = $this->feedingRepository->update($id, $dto->toPersistence());

            if ($updatedFeeding instanceof Feeding) {
                $this->feedingService->applyStockEffect($updatedFeeding, $companyId);
            }

            $latestBiometry = $this->biometryRepository->findLatestByBatch($batch->id);
            $this->alertService->checkRationDeviation(
                $batch,
                $dto->quantityProvided,
                $latestBiometry?->recommended_ration !== null
                    ? (float) $latestBiometry->recommended_ration
                    : null
            );

            return $updatedFeeding;
        });
    }
}
