<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Application\DTOs\BiometryDTO;
use App\Domain\Models\Biometry;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Infrastructure\Mappers\BiometryMapper;
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
            $mappedData = BiometryMapper::fromRequest($data);
            $biometry   = $this->biometryRepository->update($id, $mappedData);

            if (! $biometry instanceof Biometry) {
                throw new RuntimeException('Biometry not found');
            }

            return BiometryMapper::toDTO($biometry);
        });
    }
}
