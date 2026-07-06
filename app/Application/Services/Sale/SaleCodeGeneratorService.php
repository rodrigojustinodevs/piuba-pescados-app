<?php

declare(strict_types=1);

namespace App\Application\Services\Sale;

use Illuminate\Support\Facades\DB;

/**
 * Generates sequential sale codes per company in the format VND-YYYY-NNNN.
 * Uses a DB lock to guarantee uniqueness under concurrent requests.
 */
final class SaleCodeGeneratorService
{
    public function generate(string $companyId): string
    {
        $year = now()->year;

        $last = DB::table('sales')
            ->where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->whereNotNull('code')
            ->lockForUpdate()
            ->orderByDesc('code')
            ->value('code');

        $sequence = 1;

        if ($last !== null) {
            // Extract the numeric part from VND-YYYY-NNNN
            $parts    = explode('-', (string) $last);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf('VND-%d-%04d', $year, $sequence);
    }
}
