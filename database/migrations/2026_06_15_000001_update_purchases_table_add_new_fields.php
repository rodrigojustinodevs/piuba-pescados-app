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
        // Migrate existing 'confirmed' status to 'approved'
        DB::table('purchases')->where('status', 'confirmed')->update(['status' => 'approved']);

        Schema::table('purchases', function (Blueprint $table): void {
            if (Schema::hasColumn('purchases', 'purchase_date')) {
                $table->renameColumn('purchase_date', 'order_date');
            }

            if (Schema::hasColumn('purchases', 'received_at')) {
                $table->renameColumn('received_at', 'received_date');
            }

            if (! Schema::hasColumn('purchases', 'code')) {
                $table->string('code', 50)->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('purchases', 'payment_status')) {
                $table->string('payment_status', 30)->default('pending')->after('status');
            }

            if (! Schema::hasColumn('purchases', 'payment_method')) {
                $table->string('payment_method', 30)->nullable()->after('payment_status');
            }

            if (! Schema::hasColumn('purchases', 'expected_date')) {
                $table->date('expected_date')->nullable()->after('order_date');
            }

            if (! Schema::hasColumn('purchases', 'freight')) {
                $table->decimal('freight', 12, 2)->default(0)->after('total_price');
            }

            if (! Schema::hasColumn('purchases', 'other_costs')) {
                $table->decimal('other_costs', 12, 2)->default(0)->after('freight');
            }

            if (! Schema::hasColumn('purchases', 'notes')) {
                $table->text('notes')->nullable()->after('other_costs');
            }

            if (! Schema::hasColumn('purchases', 'responsible')) {
                $table->string('responsible', 255)->nullable()->after('notes');
            }
        });

        // Backfill code for records that don't have one yet
        DB::statement('SET @row := 0');
        DB::statement("
            UPDATE purchases
            JOIN (
                SELECT id, @row := @row + 1 AS rn, created_at
                FROM purchases
                ORDER BY created_at
            ) AS ranked ON purchases.id = ranked.id
            SET purchases.code = CONCAT('PC-', DATE_FORMAT(ranked.created_at, '%Y'), '-', LPAD(ranked.rn, 4, '0'))
            WHERE purchases.code IS NULL
        ");

        // Make code NOT NULL after backfill
        Schema::table('purchases', function (Blueprint $table): void {
            $table->string('code', 50)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            if (Schema::hasColumn('purchases', 'order_date')) {
                $table->renameColumn('order_date', 'purchase_date');
            }

            if (Schema::hasColumn('purchases', 'received_date')) {
                $table->renameColumn('received_date', 'received_at');
            }

            $table->dropUnique(['code']);
            $table->dropColumn(array_filter([
                Schema::hasColumn('purchases', 'code') ? 'code' : null,
                Schema::hasColumn('purchases', 'payment_status') ? 'payment_status' : null,
                Schema::hasColumn('purchases', 'payment_method') ? 'payment_method' : null,
                Schema::hasColumn('purchases', 'expected_date') ? 'expected_date' : null,
                Schema::hasColumn('purchases', 'freight') ? 'freight' : null,
                Schema::hasColumn('purchases', 'other_costs') ? 'other_costs' : null,
                Schema::hasColumn('purchases', 'notes') ? 'notes' : null,
                Schema::hasColumn('purchases', 'responsible') ? 'responsible' : null,
            ]));
        });

        DB::table('purchases')->where('status', 'approved')->update(['status' => 'confirmed']);
    }
};
