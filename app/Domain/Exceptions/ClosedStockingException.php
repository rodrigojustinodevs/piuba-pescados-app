<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class ClosedStockingException extends RuntimeException
{
    public function __construct(string $stockingId)
    {
        parent::__construct(
            "A estocagem (id: {$stockingId}) já foi encerrada (despesca total realizada). "
            . 'Não é possível registrar novas vendas para lotes encerrados.'
        );
    }
}
