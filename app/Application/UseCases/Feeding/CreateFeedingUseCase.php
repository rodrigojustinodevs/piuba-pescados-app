<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Application\DTOs\FeedingDTO;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\FeedInventoryRepositoryInterface;
use App\Domain\Repositories\FeedingRepositoryInterface;
use App\Domain\Repositories\StockRepositoryInterface;
use App\Domain\Services\Alert\AlertService;
use App\Domain\Services\FeedInventoryService\FeedInventoryService;
use App\Domain\Services\FeedInventoryService\FeedInventoryValidatorService;
use App\Infrastructure\Mappers\FeedingMapper;
use Illuminate\Support\Facades\DB;

class CreateFeedingUseCase
{
    public function __construct(
        private FeedingRepositoryInterface $feedingRepository,
        private BatchRepositoryInterface $batchRepository,
        private BiometryRepositoryInterface $biometryRepository,
        private FeedInventoryRepositoryInterface $feedInventoryRepository,
        private StockRepositoryInterface $stockRepository,
        private FeedInventoryValidatorService $feedInventoryValidatorService,
        private FeedInventoryService $feedInventoryService,
        private AlertService $alertService,
    ) {}

    public function execute(FeedingDTO $dto): FeedingDTO
{
    return DB::transaction(function () use ($dto) {
        $mappedData = FeedingMapper::fromRequest($dto->toArray());

        $batch = $this->batchRepository->showBatch('id', $mappedData['batch_id']);
        $companyId = $batch->tank?->company_id;

        $feedInventory = $this->feedInventoryRepository
            ->findByCompanyAndFeedType($companyId, $mappedData['feed_type']);

        $this->feedInventoryValidatorService
            ->validateStock($feedInventory, (float) $mappedData['stock_reduction_quantity']);

        $feeding = $this->feedingRepository->create($mappedData);
        
        $feedInventory->update(array_merge(
            $this->feedInventoryService->calculateStockAfterFeedingOperations(
                $feedInventory,
                (float) $mappedData['stock_reduction_quantity']
            ),
            ['daily_consumption' => $this->feedingRepository->getDailyConsumptionAverage($companyId, $mappedData['feed_type'])]
        ));

        $stock = $this->stockRepository->findByCompanyAndSupplyName($companyId, $mappedData['feed_type']);
        if ($stock !== null) {
            $this->stockRepository->decrementStock(
                $stock->id,
                (float) $mappedData['stock_reduction_quantity']
            );
        }

        $latestBiometry = $this->biometryRepository->findLatestByBatch($batch->id);
        $this->alertService->checkRationDeviation(
            $batch,
            (float) $mappedData['quantity_provided'],
            $latestBiometry?->recommended_ration !== null
                ? (float) $latestBiometry->recommended_ration
                : null
        );

        return FeedingMapper::toDTO($feeding);
    });
}
}
