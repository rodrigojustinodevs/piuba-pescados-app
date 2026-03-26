<?php

declare(strict_types=1);

namespace App\Application\Actions\Biometry;

use App\Domain\Exceptions\BiometryDuplicateDateException;
use App\Domain\Repositories\BiometryRepositoryInterface;

final readonly class ValidateBiometryDuplicateDateAction
{
    public function __construct(
        private BiometryRepositoryInterface $biometryRepository,
    ) {
    }

    /**
     * @throws BiometryDuplicateDateException
     */
    public function execute(string $batchId, string $date, ?string $excludeBiometryId = null): void
    {
        if ($this->biometryRepository->existsByBatchAndDate($batchId, $date, $excludeBiometryId)) {
            throw new BiometryDuplicateDateException();
        }
    }
}
