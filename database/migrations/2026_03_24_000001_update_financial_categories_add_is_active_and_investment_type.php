<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        // Migrate legacy 'income' values to 'revenue' before changing the column
        DB::table('financial_categories')
            ->where('type', 'income')
            ->update(['type' => 'revenue']);

        Schema::table('financial_categories', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('type');
        });

        // MySQL requires raw DDL to change an ENUM column
        DB::statement(
            "ALTER TABLE financial_categories MODIFY COLUMN type ENUM('revenue','expense','investment') NOT NULL"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE financial_categories MODIFY COLUMN type ENUM('income','expense') NOT NULL"
        );

        DB::table('financial_categories')
            ->where('type', 'revenue')
            ->update(['type' => 'income']);

        Schema::table('financial_categories', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });
    }
};
