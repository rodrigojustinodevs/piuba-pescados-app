<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            // Remove global unique — code must be unique per company, not globally
            $table->dropUnique('sales_code_unique');
            $table->unique(['company_id', 'code'], 'sales_company_id_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            $table->dropUnique('sales_company_id_code_unique');
            $table->unique('code', 'sales_code_unique');
        });
    }
};
