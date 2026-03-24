<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class TransactionAmountImmutableException extends RuntimeException
{
    public function __construct(public readonly string $transactionId)
    {
        parent::__construct(
            "The amount of the transaction '{$transactionId}' cannot be changed manually " .
            'because it was generated automatically by an external module (sale, purchase, etc.).'
        );
    }
}
