<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE purchases
                MODIFY COLUMN status
                    ENUM('draft','submitted','approved','partially_received','received','cancelled')
                    NOT NULL DEFAULT 'draft',
                MODIFY COLUMN payment_status
                    ENUM('pending','partial','paid')
                    NOT NULL DEFAULT 'pending',
                MODIFY COLUMN payment_method
                    ENUM('bank_slip','pix','bank_transfer','credit_card','cash','net_terms')
                    NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE purchases
                MODIFY COLUMN status         VARCHAR(30) NOT NULL DEFAULT 'draft',
                MODIFY COLUMN payment_status VARCHAR(30) NOT NULL DEFAULT 'pending',
                MODIFY COLUMN payment_method VARCHAR(30) NULL
        ");
    }
};
