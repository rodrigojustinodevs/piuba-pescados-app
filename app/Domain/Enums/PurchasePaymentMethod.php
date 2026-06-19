<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum PurchasePaymentMethod: string
{
    case BANK_SLIP     = 'bank_slip';
    case PIX           = 'pix';
    case BANK_TRANSFER = 'bank_transfer';
    case CREDIT_CARD   = 'credit_card';
    case CASH          = 'cash';
    case NET_TERMS     = 'net_terms';

    public function label(): string
    {
        return match ($this) {
            self::BANK_SLIP     => 'Boleto Bancário',
            self::PIX           => 'PIX',
            self::BANK_TRANSFER => 'Transferência Bancária',
            self::CREDIT_CARD   => 'Cartão de Crédito',
            self::CASH          => 'Dinheiro',
            self::NET_TERMS     => 'A Prazo',
        };
    }
}
