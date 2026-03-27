<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class TransferBatchOriginMismatchException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('The batch is not in the origin tank informed.');
    }
}
