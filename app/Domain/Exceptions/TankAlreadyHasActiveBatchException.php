<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class TankAlreadyHasActiveBatchException extends RuntimeException
{
    public function __construct(string $tankId)
    {
        parent::__construct("Tank [{$tankId}] already has an active batch.");
    }
}
