<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Application\DTOs\FeedingInputDTO;
use App\Application\Services\Feeding\FeedingService;
use App\Domain\Models\Feeding;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Services\Alert\AlertService;
use Illuminate\Support\Facades\DB;

final readonly class UpdateFeedingUseCase
{
    public function __construct(
        private FeedingRepositoryInterface $repository,
        private BatchRepositoryInterface $batchRepository,
        private BiometryRepositoryInterface $biometryRepository,
        private FeedingService $feedingService,
        private AlertService $alertService,
    ) {
    }

    /**
     * @param array<string, mixed> $data Validated data from the FormRequest
     */
    public function execute(string $id, array $data): Feeding
    {
        $feeding = $this->repository->findOrFail($id);
        $dto     = FeedingInputDTO::fromArray($data);
        $batch   = $this->batchRepository->findOrFail($dto->batchId);
        $companyId = $batch->tank?->company_id;

        return DB::transaction(function () use ($id, $dto, $feeding, $batch, $companyId): Feeding {
            $this->feedingService->revertStockEffect($feeding, (string) $companyId);

            $updated = $this->repository->update($id, $dto->toPersistence());

            $this->feedingService->applyStockEffect($updated, (string) $companyId);

            $latestBiometry = $this->biometryRepository->findLatestByBatch((string) $batch->id);
            $this->alertService->checkRationDeviation(
                $batch,
                $dto->quantityProvided,
                $latestBiometry?->recommended_ration !== null
                    ? (float) $latestBiometry->recommended_ration
                    : null,
            );

            return $updated;
        });
    }
}
