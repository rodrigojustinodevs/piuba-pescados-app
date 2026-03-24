<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class FinancialCategoryHasTransactionsException extends RuntimeException
{
    public function __construct(public readonly string $categoryId)
    {
        parent::__construct(
            "The financial category '{$categoryId}' cannot be deleted because it has linked transactions. " .
            'Perform the deactivation of the category instead of deleting it.'
        );
    }
}
