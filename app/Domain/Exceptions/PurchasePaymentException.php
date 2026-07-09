<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class PurchasePaymentException extends RuntimeException
{
    public static function cancelled(): self
    {
        return new self('Não é possível registrar pagamento em uma compra cancelada.');
    }

    public static function alreadyPaid(): self
    {
        return new self('Esta compra já foi totalmente paga.');
    }

    public static function amountExceedsBalance(float $amount, float $balance): self
    {
        return new self(
            "O valor do pagamento ({$amount}) excede o saldo devedor ({$balance})."
        );
    }
}
