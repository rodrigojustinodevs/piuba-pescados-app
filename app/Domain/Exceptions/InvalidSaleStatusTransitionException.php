<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\Enums\SaleStatus;
use RuntimeException;

final class InvalidSaleStatusTransitionException extends RuntimeException
{
    public function __construct(
        public readonly SaleStatus $from,
        public readonly SaleStatus $to,
    ) {
        parent::__construct(
            "Cannot transition sale from [{$from->value}] to [{$to->value}]."
        );
    }
}
