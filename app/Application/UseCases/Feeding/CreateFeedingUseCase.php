<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Application\DTOs\FeedingInputDTO;
use App\Domain\Events\FeedingCreated;
use App\Domain\Models\Feeding;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\FeedInventoryRepositoryInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use App\Domain\Services\Alert\AlertService;
use App\Domain\Services\FeedInventory\FeedInventoryService;
use App\Domain\Services\FeedInventory\FeedInventoryValidatorService;
use Illuminate\Support\Facades\DB;

final readonly class CreateFeedingUseCase
{
    public function __construct(
        private FeedingRepositoryInterface $repository,
        private BatchRepositoryInterface $batchRepository,
        private BiometryRepositoryInterface $biometryRepository,
        private FeedInventoryRepositoryInterface $feedInventoryRepository,
        private StockRepositoryInterface $stockRepository,
        private FeedInventoryValidatorService $feedInventoryValidator,
        private FeedInventoryService $feedInventoryService,
        private AlertService $alertService,
    ) {
    }

    /**
     * @param array<string, mixed> $data Validated data from the FormRequest
     */
    public function execute(array $data): Feeding
    {
        $dto           = FeedingInputDTO::fromArray($data);
        $batch         = $this->batchRepository->findOrFail($dto->batchId);
        $companyId     = $batch->tank?->company_id;
        $feedInventory = $this->feedInventoryRepository
            ->findByCompanyAndFeedType($companyId, $dto->feedType);

        $this->feedInventoryValidator->validateStock($feedInventory, $dto->stockReductionQuantity);

        return DB::transaction(function () use ($dto, $batch, $companyId, $feedInventory): Feeding {
            $feeding = $this->repository->create($dto);

            FeedingCreated::dispatch($feeding, (string) $companyId);

            $feedInventory->update(array_merge(
                $this->feedInventoryService->calculateStockAfterFeedingOperations(
                    $feedInventory,
                    $dto->stockReductionQuantity,
                ),
                [
                    'daily_consumption' => $this->repository->getDailyConsumptionAverage(
                        $companyId,
                        $dto->feedType,
                    ),
                ],
            ));

            if (! in_array($dto->stockId, [null, '', '0'], true)) {
                $this->stockRepository->decrementQuantity($dto->stockId, $dto->stockReductionQuantity);
            }

            $latestBiometry = $this->biometryRepository->findLatestByBatch((string) $batch->id);
            $this->alertService->checkRationDeviation(
                $batch,
                $dto->quantityProvided,
                $latestBiometry?->recommended_ration !== null
                    ? (float) $latestBiometry->recommended_ration
                    : null,
            );

            return $feeding;
        });
    }
}
