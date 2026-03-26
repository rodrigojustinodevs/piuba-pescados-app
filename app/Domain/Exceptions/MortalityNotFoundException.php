<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class MortalityNotFoundException extends RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Mortality [{$id}] not found.");
    }
}
