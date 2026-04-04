<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class StockWithBatchNotFoundException extends RuntimeException
{
    public function __construct(string $batchId)
    {
        parent::__construct("Stock with batch [{$batchId}] not found.");
    }
}
