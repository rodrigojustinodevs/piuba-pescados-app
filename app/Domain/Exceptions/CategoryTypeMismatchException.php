<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Domain\Enums\FinancialType;
use RuntimeException;

final class CategoryTypeMismatchException extends RuntimeException
{
    public function __construct(
        public readonly FinancialType $transactionType,
        public readonly FinancialType $categoryType,
    ) {
        parent::__construct(
            "The type of the transaction ({$transactionType->label()}) does not " .
            "match the type of the selected financial category ({$categoryType->label()}). " .
            'Select a category compatible with the type of the transaction.'
        );
    }
}
