<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use RuntimeException;

final class PurchaseReceivingException extends RuntimeException
{
    public static function cancelled(): self
    {
        return new self('Compra cancelada não pode ser recebida.');
    }

    public static function alreadyReceived(): self
    {
        return new self('Compra já foi totalmente recebida.');
    }

    public static function itemNotBelongsToPurchase(string $itemId): self
    {
        return new self("Item [{$itemId}] não pertence a esta compra.");
    }

    public static function quantityExceedsPending(float $requested, float $pending): self
    {
        return new self(
            "Quantidade solicitada ({$requested}) ultrapassa o saldo pendente ({$pending}) do item."
        );
    }
}
