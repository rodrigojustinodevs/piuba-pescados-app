<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Application\DTOs\BiometryDTO;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Infrastructure\Mappers\BiometryMapper;
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
            $mappedData = BiometryMapper::fromRequest($data);
            $biometry  = $this->biometryRepository->create($mappedData);

            return BiometryMapper::toDTO($biometry);
        });
    }
}
