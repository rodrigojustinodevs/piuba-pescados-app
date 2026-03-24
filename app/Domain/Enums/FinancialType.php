<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum FinancialType: string
{
    case REVENUE    = 'revenue';
    case EXPENSE    = 'expense';
    case INVESTMENT = 'investment';

    public function label(): string
    {
        return match ($this) {
            self::REVENUE    => 'Revenue',
            self::EXPENSE    => 'Expense',
            self::INVESTMENT => 'Investment',
        };
    }

    public function isRevenue(): bool
    {
        return $this === self::REVENUE;
    }

    public function isExpense(): bool
    {
        return $this === self::EXPENSE;
    }
}
