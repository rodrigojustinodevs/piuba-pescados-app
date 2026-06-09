<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class TransferQuantityExceedsStockException extends RuntimeException
{
    public function __construct(int $requested, int $available)
    {
        parent::__construct(
            "Transfer quantity ({$requested}) exceeds available stock ({$available})."
        );
    }
}
