<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * @var list<string>
     */
    private const array STATUS_VALUES = [
        'draft',
        'open',
        'sent',
        'expired',
        'paid',
        'approved',
        'confirmed',
        'cancelled',
        'finished',
    ];

    public function up(): void
    {
        if (! Schema::hasColumn('sales_orders', 'status')) {
            return;
        }

        DB::table('sales_orders')
            ->whereNotIn('status', self::STATUS_VALUES)
            ->update(['status' => 'draft']);

        $allowed = implode("','", self::STATUS_VALUES);

        DB::statement(
            "ALTER TABLE `sales_orders` MODIFY COLUMN `status` ENUM('{$allowed}') NOT NULL DEFAULT 'draft'"
        );
    }

    public function down(): void
    {
        if (! Schema::hasColumn('sales_orders', 'status')) {
            return;
        }

        Schema::table('sales_orders', function (Blueprint $table): void {
            $table->string('status')->default('draft')->change();
        });
    }
};
