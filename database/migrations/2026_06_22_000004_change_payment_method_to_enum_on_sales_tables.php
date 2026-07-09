<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    private const string ENUM_VALUES = "'pix','bank_slip','bank_transfer','credit_card','debit_card','cash','check'";

    public function up(): void
    {
        // Fill existing NULLs before making the column NOT NULL
        DB::statement("UPDATE sales SET payment_method = 'cash' WHERE payment_method IS NULL OR payment_method NOT IN (" . self::ENUM_VALUES . ")");

        DB::statement("ALTER TABLE sales MODIFY COLUMN payment_method ENUM(" . self::ENUM_VALUES . ") NOT NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE sale_payments MODIFY COLUMN payment_method ENUM(" . self::ENUM_VALUES . ") NOT NULL DEFAULT 'cash'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sales MODIFY COLUMN payment_method VARCHAR(30) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE sale_payments MODIFY COLUMN payment_method VARCHAR(30) NOT NULL");
    }
};
