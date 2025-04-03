<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Application\DTOs\BiometryDTO;
use App\Domain\Models\Biometry;
use App\Domain\Repositories\BiometryRepositoryInterface;
use Carbon\Carbon;
use RuntimeException;

class ShowBiometryUseCase
{
    public function __construct(
        protected BiometryRepositoryInterface $biometryRepository
    ) {
    }

    public function execute(string $id): ?BiometryDTO
    {
        $biometry = $this->biometryRepository->showBiometry('id', $id);

        if (! $biometry instanceof Biometry) {
            throw new RuntimeException('Biometry not found');
        }

        $biometryDate = $biometry->biometry_date instanceof Carbon
            ? $biometry->biometry_date
            : Carbon::parse($biometry->biometry_date);

        return new BiometryDTO(
            id: $biometry->id,
            batcheId: $biometry->batche_id,
            biometryDate: $biometryDate->toDateString(),
            averageWeight: $biometry->average_weight,
            fcr: $biometry->fcr,
            createdAt: $biometry->created_at?->toDateTimeString(),
            updatedAt: $biometry->updated_at?->toDateTimeString()
        );
    }
}
