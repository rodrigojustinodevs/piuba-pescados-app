<?php

declare(strict_types=1);

namespace App\Application\Actions\Biometry;

use App\Domain\Exceptions\BiometryNoFeedingsException;
use App\Domain\Repositories\FeedingRepositoryInterface;

final readonly class ValidateBatchHasFeedingsForBiometryAction
{
    public function __construct(
        private FeedingRepositoryInterface $feedingRepository,
    ) {
    }

    /**
     * Biometria exige que o lote já tenha registros de trato.
     *
     * @throws BiometryNoFeedingsException
     */
    public function execute(string $batchId): void
    {
        if (! $this->feedingRepository->existsByBatch($batchId)) {
            throw new BiometryNoFeedingsException();
        }
    }
}
