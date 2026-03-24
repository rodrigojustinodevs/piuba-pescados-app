<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class AllocationAmountMismatchException extends RuntimeException
{
    public function __construct(float $expected, float $actual)
    {
        parent::__construct(
            sprintf(
                'The sum of the allocated values (R$ %.2f) does not correspond to the total '
                . 'expense (R$ %.2f). Verify the percentages informed.',
                $actual,
                $expected,
            )
        );
    }
}
