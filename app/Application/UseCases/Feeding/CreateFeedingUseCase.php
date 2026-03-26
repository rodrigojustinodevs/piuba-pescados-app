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

class CreateFeedingUseCase
{
    public function __construct(
        private readonly FeedingRepositoryInterface $feedingRepository,
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly BiometryRepositoryInterface $biometryRepository,
        private readonly FeedInventoryRepositoryInterface $feedInventoryRepository,
        private readonly StockRepositoryInterface $stockRepository,
        private readonly FeedInventoryValidatorService $feedInventoryValidatorService,
        private readonly FeedInventoryService $feedInventoryService,
        private readonly AlertService $alertService,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Feeding
    {
        return DB::transaction(function () use ($data): Feeding {
            $dto = FeedingInputDTO::fromArray($data);

            $batch     = $this->batchRepository->showBatch('id', $dto->batchId);
            $companyId = $batch->tank?->company_id;

            $feedInventory = $this->feedInventoryRepository
                ->findByCompanyAndFeedType($companyId, $dto->feedType);

            $this->feedInventoryValidatorService
                ->validateStock($feedInventory, $dto->stockReductionQuantity);

            $feeding = $this->feedingRepository->create($dto->toPersistence());

            FeedingCreated::dispatch($feeding, (string) $companyId);

            $feedInventory->update(array_merge(
                $this->feedInventoryService->calculateStockAfterFeedingOperations(
                    $feedInventory,
                    $dto->stockReductionQuantity
                ),
                [
                    'daily_consumption' => $this->feedingRepository->getDailyConsumptionAverage(
                        $companyId,
                        $dto->feedType
                    ),
                ]
            ));

            if (!in_array($dto->stockId, [null, '', '0'], true)) {
                $this->stockRepository->decrementQuantity(
                    $dto->stockId,
                    $dto->stockReductionQuantity
                );
            }

            $latestBiometry = $this->biometryRepository->findLatestByBatch($batch->id);
            $this->alertService->checkRationDeviation(
                $batch,
                $dto->quantityProvided,
                $latestBiometry?->recommended_ration !== null
                    ? (float) $latestBiometry->recommended_ration
                    : null
            );

            return $feeding;
        });
    }
}
