<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE stock_transactions
             MODIFY COLUMN reference_type
             ENUM('purchase_item','feeding','adjustment','transfer','stocking','sale') NOT NULL"
        );
    }

    public function down(): void
    {
        DB::table('stock_transactions')
            ->where('reference_type', 'sale')
            ->update([
                'reference_type' => 'stocking',
            ]);

        DB::statement(
            "ALTER TABLE stock_transactions
             MODIFY COLUMN reference_type
             ENUM('purchase_item','feeding','adjustment','transfer','stocking') NOT NULL"
        );
    }
};
