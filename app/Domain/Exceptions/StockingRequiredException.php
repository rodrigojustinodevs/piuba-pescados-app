<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class StockingRequiredException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'A stocking_id is required to record a harvest sale. '
            . 'Sales without a stocking reference are not permitted.'
        );
    }
}
