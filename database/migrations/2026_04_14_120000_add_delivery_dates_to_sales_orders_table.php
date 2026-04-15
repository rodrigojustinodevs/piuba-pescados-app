<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('sales_orders', 'expected_delivery_date')) {
                $table->date('expected_delivery_date')->nullable()->after('issue_date');
            }

            if (! Schema::hasColumn('sales_orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('expected_delivery_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table): void {
            $columnsToDrop = array_values(array_filter([
                Schema::hasColumn('sales_orders', 'delivered_at') ? 'delivered_at' : null,
                Schema::hasColumn('sales_orders', 'expected_delivery_date') ? 'expected_delivery_date' : null,
            ]));

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
