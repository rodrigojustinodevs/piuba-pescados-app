<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class StockMovementException extends RuntimeException
{
    public static function insufficientBalance(float $requested, float $available): self
    {
        return new self(
            sprintf(
                'Saldo insuficiente: solicitado %.3f, disponível %.3f.',
                $requested,
                $available,
            ),
        );
    }

    public static function invalidTransfer(string $reason): self
    {
        return new self($reason);
    }

    public static function sameStock(): self
    {
        return new self('Estoque de origem e destino não podem ser iguais.');
    }
}
