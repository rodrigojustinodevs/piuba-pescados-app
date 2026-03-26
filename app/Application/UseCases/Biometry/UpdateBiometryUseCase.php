<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Application\DTOs\BiometryInputDTO;
use App\Domain\Models\Biometry;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Services\Alert\AlertService;
use App\Domain\Services\Biometry\BiometryFcrService;
use App\Domain\Services\Biometry\BiometryValidatorService;
use App\Domain\Services\Feeding\FeedingService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateBiometryUseCase
{
    public function __construct(
        private readonly BiometryRepositoryInterface $biometryRepository,
        private readonly BatchRepositoryInterface $batchRepository,
        private readonly BiometryValidatorService $biometryValidator,
        private readonly BiometryFcrService $biometryFcrService,
        private readonly FeedingService $feedingService,
        private readonly AlertService $alertService,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): Biometry
    {
        return DB::transaction(function () use ($id, $data): Biometry {
            $biometry = $this->biometryRepository->showBiometry('id', $id);

            $batch = $this->batchRepository->showBatch('id', $biometry->batch_id);

            $dto = BiometryInputDTO::fromArray($data);

            $averageWeight = $this->biometryFcrService->calculateAverageWeight(
                (float) $dto->sampleWeight,
                (int) $dto->sampleQuantity,
                $dto->averageWeight
            );

            $biometryDate     = $dto->biometryDate ?: $biometry->biometry_date;
            $biomassEstimated = $averageWeight * (int) $batch->initial_quantity;

            $this->biometryValidator->validateAverageWeight($averageWeight);

            $fcr = $this->biometryFcrService->calculate(
                $batch,
                $averageWeight,
                $biometryDate
            );

            $capacityLiters = (int) ($batch->tank->capacity_liters ?? 0);
            $density        = $this->feedingService->calculateDensity($biomassEstimated, $capacityLiters);
            $sampleQuantity = (int) ($dto->sampleQuantity
                ?? $biometry->sample_quantity
                ?? $batch->initial_quantity);
            $dailyRecommendation = $this->feedingService->getDailyRecommendation($averageWeight, $sampleQuantity);

            $updatePayload = [
                'batch_id'           => $biometry->batch_id,
                'biometry_date'      => $biometryDate,
                'average_weight'     => $averageWeight,
                'fcr'                => $fcr,
                'sample_weight'      => (float) ($dto->sampleWeight ?? $biometry->sample_weight ?? 0),
                'sample_quantity'    => (int) ($dto->sampleQuantity ?? $biometry->sample_quantity ?? 0),
                'biomass_estimated'  => $biomassEstimated,
                'density_at_time'    => $density,
                'recommended_ration' => $dailyRecommendation,
            ];

            $updated = $this->biometryRepository->update($id, $updatePayload);

            if (! $updated instanceof Biometry) {
                throw new RuntimeException('Biometry not found');
            }

            $this->alertService->checkDensityAlert($batch, (float) $updated->density_at_time);
            $this->alertService->checkHighFcr($batch, (float) $updated->fcr);

            return $updated;
        });
    }
}
