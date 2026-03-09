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
use App\Domain\Services\FeedInventoryService\FeedInventoryValidatorService;
use App\Domain\Services\Feeding\FeedingService;
use App\Domain\Services\FeedInventoryService\FeedInventoryService;
use App\Infrastructure\Mappers\FeedingMapper;
use Illuminate\Support\Facades\DB;

class UpdateFeedingUseCase
{
    public function __construct(
        private readonly FeedingRepositoryInterface $feedingRepository,
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly BiometryRepositoryInterface $biometryRepository,
        private readonly FeedInventoryRepositoryInterface $feedInventoryRepository,
        private readonly StockRepositoryInterface $stockRepository,
        private readonly FeedInventoryValidatorService $feedInventoryValidatorService,
        private readonly FeedingService $feedingService,
        private readonly FeedInventoryService $feedInventoryService,
        private readonly AlertService $alertService,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): FeedingDTO
    {
        return DB::transaction(function () use ($id, $data): FeedingDTO {
            $feeding = $this->feedingRepository->showFeeding('id', $id);

            if (!$feeding) {
                throw new \RuntimeException('Feeding not found');
            }

            $mappedData = FeedingMapper::fromRequest($data);
            $batch = $this->batchRepository->showBatch('id', $mappedData['batch_id']);
            $companyId = $batch->tank?->company_id;

            $this->feedingService->revertStockEffect($feeding, $companyId);

            
            $updatedFeeding = $this->feedingRepository->update($id, $mappedData);
            
            $this->feedingService->applyStockEffect($feeding, $companyId);

            $latestBiometry = $this->biometryRepository->findLatestByBatch($batch->id);
            $this->alertService->checkRationDeviation(
                $batch,
                (float) $mappedData['quantity_provided'],
                $latestBiometry?->recommended_ration !== null
                    ? (float) $latestBiometry->recommended_ration
                    : null
            );

            return FeedingMapper::toDTO($updatedFeeding);
        });
    }
}  
