<?php

declare(strict_types=1);

namespace App\Domain\Services\Biometry;

use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Domain\Repositories\FeedingRepositoryInterface;
use Illuminate\Validation\ValidationException;

class BiometryValidatorService
{
    public function __construct(
        private readonly FeedingRepositoryInterface $feedingRepository,
        private readonly BiometryRepositoryInterface $biometryRepository,
    ) {
    }

    public function validateAverageWeight(float $weight): void
    {
        if ($weight <= 0) {
            throw ValidationException::withMessages([
                'average_weight' => __('validation.biometry.weight_not_positive'),
            ]);
        }
    }

    public function validateHasFeedings(string $batchId): void
    {
        if (! $this->feedingRepository->existsByBatch($batchId)) {
            throw ValidationException::withMessages([
                'batch_id' => __('validation.biometry.no_feedings'),
            ]);
        }
    }

    public function validateNoDuplicateDate(string $batchId, string $date): void
    {
        $existing = $this->biometryRepository->showBiometry('batch_id', $batchId);

        if (! $existing instanceof \App\Domain\Models\Biometry) {
            return;
        }
        $existingDate    = $existing->biometry_date;
        $existingDateStr = $existingDate instanceof \DateTimeInterface
            ? $existingDate->format('Y-m-d')
            : (string) $existingDate;

        if ($existingDateStr === $date) {
            throw ValidationException::withMessages([
                'biometry_date' => __('validation.biometry.duplicate_date'),
            ]);
        }
    }
}
