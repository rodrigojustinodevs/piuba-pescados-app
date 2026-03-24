<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum FinancialTransactionReferenceType: string
{
    case SALE            = 'sale';
    case PURCHASE_ITEM   = 'purchase_item';
    case COST_ALLOCATION = 'cost_allocation';

    public function label(): string
    {
        return match ($this) {
            self::SALE            => 'Sale',
            self::PURCHASE_ITEM   => 'Purchase Item',
            self::COST_ALLOCATION => 'Cost Allocation',
        };
    }
}
