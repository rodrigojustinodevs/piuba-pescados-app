<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Application\Actions\Biometry\ValidateBatchHasFeedingsForBiometryAction;
use App\Application\Actions\Biometry\ValidateBiometryAverageWeightAction;
use App\Application\Actions\Biometry\ValidateBiometryDuplicateDateAction;
use App\Application\DTOs\BiometryInputDTO;
use App\Application\DTOs\GrowthCurveInputDTO;
use App\Application\Services\Biometry\BiometryFcrService;
use App\Application\Services\Feeding\FeedingService;
use App\Domain\Models\Biometry;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\GrowthCurveRepositoryInterface;
use App\Domain\Services\Alert\AlertService;
use Illuminate\Support\Facades\DB;

final readonly class CreateBiometryUseCase
{
    public function __construct(
        private BiometryRepositoryInterface $biometryRepository,
        private BatchRepositoryInterface $batchRepository,
        private ValidateBiometryAverageWeightAction $validateBiometryAverageWeight,
        private ValidateBatchHasFeedingsForBiometryAction $validateBatchHasFeedingsForBiometry,
        private ValidateBiometryDuplicateDateAction $validateBiometryDuplicateDate,
        private BiometryFcrService $biometryFcrService,
        private GrowthCurveRepositoryInterface $growthCurveRepository,
        private AlertService $alertService,
        private FeedingService $feedingService,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Biometry
    {
        return DB::transaction(function () use ($data): Biometry {
            $dto   = BiometryInputDTO::fromArray($data);
            $batch = $this->batchRepository->findOrFail($dto->batchId);

            $averageWeight = $this->biometryFcrService->calculateAverageWeight(
                (float) $dto->sampleWeight,
                (int) $dto->sampleQuantity,
                $dto->averageWeight,
            );
            $biomassEstimated = $averageWeight * (int) $batch->initial_quantity;

            $this->validateBiometryAverageWeight->execute($averageWeight);
            $this->validateBatchHasFeedingsForBiometry->execute($batch->id);
            $this->validateBiometryDuplicateDate->execute($batch->id, $dto->biometryDate);

            $fcr = $this->biometryFcrService->calculate($batch, $averageWeight, $dto->biometryDate);

            $capacityLiters      = (int) ($batch->tank->capacity_liters ?? 0);
            $density             = $this->feedingService->calculateDensity($biomassEstimated, $capacityLiters);
            $dailyRecommendation = $this->feedingService->getDailyRecommendation(
                $averageWeight,
                $dto->sampleQuantity,
            );

            $biometry = $this->biometryRepository->create([
                'batch_id'           => $batch->id,
                'biometry_date'      => $dto->biometryDate,
                'average_weight'     => $averageWeight,
                'fcr'                => $fcr,
                'sample_weight'      => $dto->sampleWeight,
                'sample_quantity'    => $dto->sampleQuantity,
                'biomass_estimated'  => $biomassEstimated,
                'density_at_time'    => $density,
                'recommended_ration' => $dailyRecommendation,
            ]);

            $this->growthCurveRepository->create(new GrowthCurveInputDTO(
                batchId:       (string) $batch->id,
                averageWeight: (float) $biometry->average_weight,
            ));

            $this->alertService->checkDensityAlert($batch, (float) $biometry->density_at_time);
            $this->alertService->checkHighFcr($batch, (float) $biometry->fcr);

            return $biometry;
        });
    }
}
