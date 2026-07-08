<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE users MODIFY COLUMN status "
            . "ENUM('active','inactive','blocked','pending') "
            . "NOT NULL DEFAULT 'pending'",
        );
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'");
    }
};
