<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum StockTransactionReferenceType: string
{
    case PURCHASE_ITEM = 'purchase_item';
    case FEEDING       = 'feeding';
    case ADJUSTMENT    = 'adjustment';
    case TRANSFER      = 'transfer';
    case STOCKING      = 'stocking';
    case SALE          = 'sale';

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE_ITEM => 'Purchase Item',
            self::FEEDING       => 'Feeding',
            self::ADJUSTMENT    => 'Adjustment',
            self::TRANSFER      => 'Transfer',
            self::STOCKING      => 'Stocking',
            self::SALE          => 'Sale',
        };
    }
}
