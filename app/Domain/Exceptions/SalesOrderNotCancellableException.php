<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class SalesOrderNotCancellableException extends RuntimeException
{
    public function __construct(string $orderId)
    {
        parent::__construct(
            "The sales order \"{$orderId}\" cannot be cancelled anymore. "
            . 'Only sales orders with status draft, pending or confirmed can be cancelled.'
        );
    }
}
