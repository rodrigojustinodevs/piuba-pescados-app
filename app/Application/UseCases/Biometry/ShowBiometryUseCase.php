<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Domain\Models\Biometry;
use App\Domain\Repositories\BiometryRepositoryInterface;
use RuntimeException;

class ShowBiometryUseCase
{
    public function __construct(
        private readonly BiometryRepositoryInterface $biometryRepository,
    ) {
    }

    public function execute(string $id): Biometry
    {
        $biometry = $this->biometryRepository->showBiometry('id', $id);

        if (! $biometry instanceof Biometry) {
            throw new RuntimeException('Biometry not found');
        }

        return $biometry;
    }
}
