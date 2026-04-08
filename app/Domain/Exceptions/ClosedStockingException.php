<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class ClosedStockingException extends RuntimeException
{
    public function __construct(string $stockingId)
    {
        parent::__construct(
            "The stocking (id: {$stockingId}) has already been closed (total harvest completed). "
            . 'New registration is not allowed for closed batches.'
        );
    }
}
