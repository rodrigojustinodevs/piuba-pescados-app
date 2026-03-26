<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class MortalityExceedsSurvivorsException extends RuntimeException
{
    public function __construct(int $survivors, int $requested)
    {
        parent::__construct(
            "Operação inválida. O lote possui apenas {$survivors} peixes vivos, "
            . "mas você tentou registrar {$requested} mortes."
        );
    }
}
