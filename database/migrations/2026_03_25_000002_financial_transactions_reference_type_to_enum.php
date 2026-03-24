<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    /**
     * Converts reference_type from VARCHAR(50) to ENUM aligned with
     * FinancialTransactionReferenceType: sale | purchase_item | cost_allocation.
     *
     * Any row with a value outside the allowed set is cleared (set to NULL)
     * before the column type change to avoid MySQL rejecting the conversion.
     */
    public function up(): void
    {
        $allowed = ['sale', 'purchase_item', 'cost_allocation'];

        DB::table('financial_transactions')
            ->whereNotNull('reference_type')
            ->whereNotIn('reference_type', $allowed)
            ->update(['reference_type' => null, 'reference_id' => null]);

        DB::statement(
            "ALTER TABLE financial_transactions
             MODIFY COLUMN reference_type
             ENUM('sale','purchase_item','cost_allocation') NULL"
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE financial_transactions
             MODIFY COLUMN reference_type VARCHAR(50) NULL'
        );
    }
};
