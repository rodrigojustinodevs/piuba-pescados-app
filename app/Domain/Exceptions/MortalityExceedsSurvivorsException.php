<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class MortalityExceedsSurvivorsException extends RuntimeException
{
    public function __construct(int $survivors, int $requested)
    {
        parent::__construct(
            "Operation invalid. The batch has only {$survivors} live fish, "
            . "but you tried to register {$requested} deaths."
        );
    }
}
