<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Application\DTOs\BiometryDTO;
use App\Domain\Models\Biometry;
use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Infrastructure\Mappers\BiometryMapper;
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

        return BiometryMapper::toDTO($biometry);
    }
}
