<?php

declare(strict_types=1);

namespace App\Domain\Enums;

enum FinancialCategoryType: string
{
    case INCOME  = 'income';
    case EXPENSE = 'expense';
}
