<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Application\DTOs\BiometryDTO;
use App\Domain\Repositories\BiometryRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateBiometryUseCase
{
    public function __construct(
        protected BiometryRepositoryInterface $biometryRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): BiometryDTO
    {
        return DB::transaction(function () use ($data): BiometryDTO {
            $biometry = $this->biometryRepository->create($data);

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
        });
    }
}
