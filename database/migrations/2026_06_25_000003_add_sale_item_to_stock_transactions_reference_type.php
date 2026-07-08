<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE stock_transactions
            MODIFY COLUMN reference_type
            ENUM('purchase_item','feeding','adjustment','transfer','stocking','sale','sale_item')
            NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE stock_transactions
            SET reference_type = 'sale'
            WHERE reference_type = 'sale_item'
        ");

        DB::statement("
            ALTER TABLE stock_transactions
            MODIFY COLUMN reference_type
            ENUM('purchase_item','feeding','adjustment','transfer','stocking','sale')
            NOT NULL
        ");
    }
};
