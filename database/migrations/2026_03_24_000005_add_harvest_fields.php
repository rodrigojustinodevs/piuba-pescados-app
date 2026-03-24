<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        // ── 1. stockings: add status and closed_at ────────────────────────────
        Schema::table('stockings', function (Blueprint $table): void {
            $table->enum('status', ['active', 'closed'])
                ->default('active')
                ->after('accumulated_fixed_cost');

            $table->dateTime('closed_at')
                ->nullable()
                ->after('status');
        });

        // ── 2. sales: add is_total_harvest ────────────────────────────────────
        Schema::table('sales', function (Blueprint $table): void {
            $table->boolean('is_total_harvest')
                ->default(false)
                ->after('notes');
        });

        // ── 3. stock_transactions: make supply_id nullable ───────────────────
        // Biomass outflow transactions (harvest/sale) do not reference a supply.
        Schema::table('stock_transactions', function (Blueprint $table): void {
            $table->uuid('supply_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table): void {
            $table->uuid('supply_id')->nullable(false)->change();
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->dropColumn('is_total_harvest');
        });

        Schema::table('stockings', function (Blueprint $table): void {
            $table->dropColumn(['status', 'closed_at']);
        });
    }
};
