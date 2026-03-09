<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Application\DTOs\BiometryDTO;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use App\Domain\Services\Biometry\BiometryValidatorService;
use App\Domain\Services\Biometry\BiometryFcrService;
use App\Domain\Services\Alert\AlertService;
use App\Domain\Services\Feeding\FeedingService;
use App\Infrastructure\Mappers\BiometryMapper;
use Illuminate\Support\Facades\DB;

class CreateBiometryUseCase
{
    public function __construct(
        private BiometryRepositoryInterface $biometryRepository,
        private BatchRepositoryInterface $batchRepository,
        private BiometryValidatorService $biometryValidator,
        private BiometryFcrService $biometryFcrService,
        private GrowthCurveRepositoryInterface $growthCurveRepository,
        private AlertService $alertService,
        private FeedingService $feedingService,
    ) {}

    public function execute(array $data): BiometryDTO
    {
        return DB::transaction(function () use ($data): BiometryDTO {
            $mappedData = BiometryMapper::fromRequest($data);

            $batch = $this->batchRepository->showBatch('id', $mappedData['batch_id']);
            
            $averageWeight = $this->biometryFcrService->calculateAverageWeight(
                (float) $mappedData['sample_weight'],
                (int) $mappedData['sample_quantity'],
                (float) $mappedData['average_weight']
            );
            
            $biomassEstimated = $averageWeight * (int) $batch->initial_quantity;

            $this->biometryValidator->validateAverageWeight($averageWeight);
            $this->biometryValidator->validateHasFeedings($batch->id);
            $this->biometryValidator->validateNoDuplicateDate(
                $batch->id,
                $mappedData['biometry_date']
            );

            $fcr = $this->biometryFcrService->calculate(
                $batch,
                $averageWeight,
                $mappedData['biometry_date']
            );

            $density = $this->feedingService->calculateDensity($biomassEstimated, (int) $batch->tank?->capacity_liters);
            $dailyRecommendation = $this->feedingService->getDailyRecommendation($averageWeight, $mappedData['sample_quantity']);

            $createPayload = [
                'batch_id'       => $batch->id,
                'biometry_date'  => $mappedData['biometry_date'],
                'average_weight' => $averageWeight,
                'fcr'            => $fcr,
                'sample_weight'  => $mappedData['sample_weight'],
                'sample_quantity' => $mappedData['sample_quantity'],
                'biomass_estimated' => $biomassEstimated,
                'density_at_time' => $density,
                'recommended_ration' => $dailyRecommendation,
            ];

            $biometry = $this->biometryRepository->create($createPayload);

            $this->growthCurveRepository->create([
                'batch_id'       => $batch->id,
                'average_weight' => (float) $biometry->average_weight,
            ]);

            $this->alertService->checkDensityAlert($batch, (float) $biometry->density_at_time);
            $this->alertService->checkHighFcr($batch, (float) $biometry->fcr);

            return BiometryMapper::toDTO($biometry);
        });
    }
}