<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE users MODIFY COLUMN position "
            . "ENUM('admin','general_manager','production_manager','field_operator',"
            . "'sales_manager','sales_rep','financial_analyst','billing_clerk','logistics_dispatcher') "
            . 'NULL',
        );
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users MODIFY COLUMN position VARCHAR(30) NULL');
    }
};
