<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class BiometryNotFoundException extends RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Biometry [{$id}] not found.");
    }
}
