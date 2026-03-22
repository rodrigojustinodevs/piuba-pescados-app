<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table): void {
            $table->decimal('total_cost', 15, 2)
                ->unsigned()
                ->default(0.00)
                ->after('unit_price')
                ->comment('Total financial value: quantity * unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table): void {
            $table->dropColumn('total_cost');
        });
    }
};
