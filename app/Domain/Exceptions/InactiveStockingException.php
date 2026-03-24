<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class InactiveStockingException extends RuntimeException
{
    public function __construct(string $stockingId, string $batchStatus)
    {
        parent::__construct(
            "The batch linked to the stocking (id: {$stockingId}) is not active "
            . "(current status: {$batchStatus}). Only active batches can receive cost allocations."
        );
    }
}
