<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class ClientCreditLimitExceededException extends RuntimeException
{
    public function __construct(string $clientName, float $creditLimit, float $currentExposure, float $newSaleAmount)
    {
        $total = $currentExposure + $newSaleAmount;

        parent::__construct(
            "The client (name: {$clientName}) has exceeded the credit limit. "
            . "Limit: R$ {$creditLimit} | Current exposure:"
            . "R$ {$currentExposure} | New sale: R$ {$newSaleAmount} | Total: R$ {$total}."
        );
    }
}
