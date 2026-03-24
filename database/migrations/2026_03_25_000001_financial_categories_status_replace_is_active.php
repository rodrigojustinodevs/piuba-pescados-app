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
        Schema::table('financial_categories', function (Blueprint $table): void {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('type');
        });

        DB::table('financial_categories')
            ->where('is_active', false)
            ->update(['status' => 'inactive']);

        Schema::table('financial_categories', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('financial_categories', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('type');
        });

        DB::table('financial_categories')
            ->where('status', 'inactive')
            ->update(['is_active' => false]);

        Schema::table('financial_categories', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
