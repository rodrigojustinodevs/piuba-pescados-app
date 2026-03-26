<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Domain\Models\Biometry;
use App\Domain\Repositories\BiometryRepositoryInterface;

final readonly class ShowBiometryUseCase
{
    public function __construct(
        private BiometryRepositoryInterface $biometryRepository,
    ) {
    }

    public function execute(string $id): Biometry
    {
        return $this->biometryRepository->findOrFail($id);
    }
}
