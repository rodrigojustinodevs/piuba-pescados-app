<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Application\Actions\Biometry\ValidateBiometryAverageWeightAction;
use App\Application\Actions\Biometry\ValidateBiometryDuplicateDateAction;
use App\Application\DTOs\BiometryInputDTO;
use App\Application\Services\Biometry\BiometryFcrService;
use App\Application\Services\Feeding\FeedingService;
use App\Domain\Models\Biometry;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Services\Alert\AlertService;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateBiometryUseCase
{
    public function __construct(
        private BiometryRepositoryInterface $biometryRepository,
        private BatchRepositoryInterface $batchRepository,
        private ValidateBiometryAverageWeightAction $validateBiometryAverageWeight,
        private ValidateBiometryDuplicateDateAction $validateBiometryDuplicateDate,
        private BiometryFcrService $biometryFcrService,
        private FeedingService $feedingService,
        private AlertService $alertService,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): Biometry
    {
        return DB::transaction(function () use ($id, $data): Biometry {
            $biometry = $this->biometryRepository->findOrFail($id);
            $batch    = $this->batchRepository->findOrFail($biometry->batch_id);
            $dto      = BiometryInputDTO::fromArray($data);

            $averageWeight = $this->biometryFcrService->calculateAverageWeight(
                (float) $dto->sampleWeight,
                (int) $dto->sampleQuantity,
                $dto->averageWeight,
            );

            $biometryDate     = $dto->biometryDate ?: $biometry->biometry_date;
            $biomassEstimated = $averageWeight * (int) $batch->initial_quantity;

            $this->validateBiometryAverageWeight->execute($averageWeight);

            $biometryDateStr = $biometryDate instanceof DateTimeInterface
                ? $biometryDate->format('Y-m-d')
                : (string) $biometryDate;
            $this->validateBiometryDuplicateDate->execute($batch->id, $biometryDateStr, $id);

            $fcr = $this->biometryFcrService->calculate($batch, $averageWeight, $biometryDateStr);

            $capacityLiters = (int) ($batch->tank->capacity_liters ?? 0);
            $density        = $this->feedingService->calculateDensity($biomassEstimated, $capacityLiters);
            $sampleQuantity = (int) (
                $dto->sampleQuantity ?? $biometry->sample_quantity ?? $batch->initial_quantity
            );
            $dailyRecommendation = $this->feedingService->getDailyRecommendation($averageWeight, $sampleQuantity);

            $updated = $this->biometryRepository->update($id, [
                'batch_id'           => $biometry->batch_id,
                'biometry_date'      => $biometryDate,
                'average_weight'     => $averageWeight,
                'fcr'                => $fcr,
                'sample_weight'      => (float) ($dto->sampleWeight ?? $biometry->sample_weight ?? 0),
                'sample_quantity'    => (int) ($dto->sampleQuantity ?? $biometry->sample_quantity ?? 0),
                'biomass_estimated'  => $biomassEstimated,
                'density_at_time'    => $density,
                'recommended_ration' => $dailyRecommendation,
            ]);

            $this->alertService->checkDensityAlert($batch, (float) $updated->density_at_time);
            $this->alertService->checkHighFcr($batch, (float) $updated->fcr);

            return $updated;
        });
    }
}
