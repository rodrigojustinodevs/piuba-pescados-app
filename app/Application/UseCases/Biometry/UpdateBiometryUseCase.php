<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Application\DTOs\BiometryDTO;
use App\Domain\Models\Biometry;
use App\Domain\Repositories\BiometryRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateBiometryUseCase
{
    public function __construct(
        protected BiometryRepositoryInterface $biometryRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): BiometryDTO
    {
        return DB::transaction(function () use ($id, $data): BiometryDTO {
            $biometry = $this->biometryRepository->update($id, $data);

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
        });
    }
}
