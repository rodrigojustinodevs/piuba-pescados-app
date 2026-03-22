<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly float $requested,
        public readonly float $available,
    ) {
        parent::__construct(
            "Insufficient stock: requested {$requested}, available {$available}."
        );
    }
}
