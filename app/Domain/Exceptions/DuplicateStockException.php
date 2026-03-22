<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class DuplicateStockException extends RuntimeException
{
    public function __construct(
        public readonly string $companyId,
        public readonly string $supplyId,
    ) {
        parent::__construct(
            "Stock already exists for company {$companyId} and supply {$supplyId}."
        );
    }
}

