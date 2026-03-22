<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class StockNotFoundException extends RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Stock [{$id}] not found.");
    }
}