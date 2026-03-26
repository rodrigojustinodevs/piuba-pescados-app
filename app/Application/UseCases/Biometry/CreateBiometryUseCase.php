<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Application\DTOs\BiometryInputDTO;
use App\Domain\Models\Biometry;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use App\Domain\Services\Alert\AlertService;
use App\Domain\Services\Biometry\BiometryFcrService;
use App\Domain\Services\Biometry\BiometryValidatorService;
use App\Domain\Services\Feeding\FeedingService;
use Illuminate\Support\Facades\DB;

class CreateBiometryUseCase
{
    public function __construct(
        private readonly BiometryRepositoryInterface $biometryRepository,
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly BiometryValidatorService $biometryValidator,
        private readonly BiometryFcrService $biometryFcrService,
        private readonly GrowthCurveRepositoryInterface $growthCurveRepository,
        private readonly AlertService $alertService,
        private readonly FeedingService $feedingService,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Biometry
    {
        return DB::transaction(function () use ($data): Biometry {
            $dto = BiometryInputDTO::fromArray($data);

            $batch = $this->batchRepository->showBatch('id', $dto->batchId);

            $averageWeight = $this->biometryFcrService->calculateAverageWeight(
                (float) $dto->sampleWeight,
                (int) $dto->sampleQuantity,
                $dto->averageWeight
            );

            $biomassEstimated = $averageWeight * (int) $batch->initial_quantity;

            $this->biometryValidator->validateAverageWeight($averageWeight);
            $this->biometryValidator->validateHasFeedings($batch->id);
            $this->biometryValidator->validateNoDuplicateDate(
                $batch->id,
                $dto->biometryDate
            );

            $fcr = $this->biometryFcrService->calculate(
                $batch,
                $averageWeight,
                $dto->biometryDate
            );

            $capacityLiters      = (int) ($batch->tank->capacity_liters ?? 0);
            $density             = $this->feedingService->calculateDensity($biomassEstimated, $capacityLiters);
            $dailyRecommendation = $this->feedingService->getDailyRecommendation(
                $averageWeight,
                $dto->sampleQuantity
            );

            $createPayload = [
                'batch_id'           => $batch->id,
                'biometry_date'      => $dto->biometryDate,
                'average_weight'     => $averageWeight,
                'fcr'                => $fcr,
                'sample_weight'      => $dto->sampleWeight,
                'sample_quantity'    => $dto->sampleQuantity,
                'biomass_estimated'  => $biomassEstimated,
                'density_at_time'    => $density,
                'recommended_ration' => $dailyRecommendation,
            ];

            $biometry = $this->biometryRepository->create($createPayload);

            $this->growthCurveRepository->create([
                'batch_id'       => $batch->id,
                'average_weight' => (float) $biometry->average_weight,
            ]);

            $this->alertService->checkDensityAlert($batch, (float) $biometry->density_at_time);
            $this->alertService->checkHighFcr($batch, (float) $biometry->fcr);

            return $biometry;
        });
    }
}
