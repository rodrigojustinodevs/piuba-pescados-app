<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE financial_transactions
             MODIFY COLUMN type ENUM('revenue','expense','investment','purchase','transfer') NOT NULL"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE financial_transactions
             MODIFY COLUMN type ENUM('revenue','expense','investment','purchase') NOT NULL"
        );
    }
};
