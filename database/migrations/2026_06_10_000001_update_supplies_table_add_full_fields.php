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
        // Convert unit from enum to varchar(50) to allow any unit string
        DB::statement("ALTER TABLE supplies MODIFY COLUMN unit varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kg'");

        // Convert category from nullable varchar to enum
        DB::statement("UPDATE supplies SET category = 'other' WHERE category IS NOT NULL AND category NOT IN ('feed','medication','fertilizer','probiotic','equipment','packaging','finished_product','other')");
        DB::statement("ALTER TABLE supplies MODIFY COLUMN category ENUM('feed','medication','fertilizer','probiotic','equipment','packaging','finished_product','other') COLLATE utf8mb4_unicode_ci DEFAULT 'other'");

        Schema::table('supplies', function (Blueprint $table): void {
            $table->string('sku', 100)->nullable()->unique()->after('id');
            $table->decimal('unit_cost', 15, 2)->default(0)->after('unit');
            $table->decimal('sale_price', 15, 2)->default(0)->after('unit_cost');
            $table->decimal('current_stock', 15, 3)->default(0)->after('sale_price');
            $table->decimal('min_stock', 15, 3)->default(0)->after('current_stock');
            $table->uuid('supplier_id')->nullable()->after('min_stock');
            $table->boolean('is_product')->default(false)->after('supplier_id');
            $table->text('description')->nullable()->after('is_product');
            $table->enum('status', ['active', 'inactive', 'low_stock'])->default('active')->after('description');

            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table): void {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn([
                'sku', 'unit_cost', 'sale_price', 'current_stock',
                'min_stock', 'supplier_id', 'is_product', 'description', 'status',
            ]);
        });

        DB::statement("ALTER TABLE supplies MODIFY COLUMN unit ENUM('kg','g','liter','ml','unit','box','piece') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kg'");
        DB::statement("ALTER TABLE supplies MODIFY COLUMN category varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL");
    }
};
