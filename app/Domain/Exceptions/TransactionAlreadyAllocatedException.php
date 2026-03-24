<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class TransactionAlreadyAllocatedException extends RuntimeException
{
    public function __construct(string $transactionId)
    {
        parent::__construct(
            "The financial transaction (id: {$transactionId}) has already been allocated. "
            . 'Remove the existing allocation before creating a new one.'
        );
    }
}
