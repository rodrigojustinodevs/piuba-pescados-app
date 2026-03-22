<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\Enums\PurchaseStatus;
use RuntimeException;

final class InvalidPurchaseStatusTransitionException extends RuntimeException
{
    public function __construct(
        public readonly PurchaseStatus $from,
        public readonly PurchaseStatus $to,
    ) {
        parent::__construct(
            "Cannot transition purchase from [{$from->value}] to [{$to->value}]."
        );
    }
}