<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class BatchAlreadyFinishedException extends RuntimeException
{
    public function __construct(string $batchId)
    {
        parent::__construct("Batch [{$batchId}] has already been finished.");
    }
}
