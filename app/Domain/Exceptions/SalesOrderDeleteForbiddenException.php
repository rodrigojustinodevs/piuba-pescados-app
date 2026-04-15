<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\Enums\SalesOrderStatus;
use RuntimeException;

final class SalesOrderDeleteForbiddenException extends RuntimeException
{
    public static function forStatus(SalesOrderStatus $status): self
    {
        return new self(
            "Sales order in status [{$status->value}] cannot be deleted."
        );
    }
}
