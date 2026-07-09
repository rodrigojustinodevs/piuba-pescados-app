<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class SensorNotAssignedToCompanyException extends RuntimeException
{
    public function __construct(string $sensorId)
    {
        parent::__construct(
            "Sensor [{$sensorId}] is not assigned to any company."
        );
    }
}
