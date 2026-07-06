<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum PaymentMethod: string
{
    case BANK_SLIP     = 'bank_slip';
    case BANK_TRANSFER = 'bank_transfer';
    case CREDIT_CARD   = 'credit_card';
    case DEBIT_CARD    = 'debit_card';
    case CASH          = 'cash';
    case PIX           = 'pix';
    case CHECK         = 'check';

    public function label(): string
    {
        return match ($this) {
            self::BANK_SLIP     => 'Boleto Bancário',
            self::BANK_TRANSFER => 'Transferência Bancária',
            self::CREDIT_CARD   => 'Cartão de Crédito',
            self::DEBIT_CARD    => 'Cartão de Débito',
            self::CASH          => 'Dinheiro',
            self::PIX           => 'PIX',
            self::CHECK         => 'Cheque',
        };
    }
}
