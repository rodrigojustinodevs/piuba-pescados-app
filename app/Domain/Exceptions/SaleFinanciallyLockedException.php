<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class SaleFinanciallyLockedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'It is not possible to edit the values of a sale with registered receipts.',
        );
    }
}
