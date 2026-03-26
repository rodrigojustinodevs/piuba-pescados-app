<?php

declare(strict_types=1);

namespace App\Application\Actions\Biometry;

use App\Domain\Exceptions\BiometryAverageWeightInvalidException;

final readonly class ValidateBiometryAverageWeightAction
{
    /**
     * @throws BiometryAverageWeightInvalidException
     */
    public function execute(float $weight): void
    {
        if ($weight <= 0) {
            throw new BiometryAverageWeightInvalidException();
        }
    }
}
