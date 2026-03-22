<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class ZeroDeltaException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'No adjustment needed: the physical quantity matches the system quantity.'
        );
    }
}
