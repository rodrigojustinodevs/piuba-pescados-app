<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     * Itens de compra (uma compra pode ter vários insumos).
     */
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('purchase_id');
            $table->uuid('supply_id');
            $table->decimal('quantity', 15, 4)->unsigned();
            $table->string('unit', 50)->default('kg');
            $table->decimal('unit_price', 15, 4)->unsigned();
            $table->decimal('total_price', 15, 2)->unsigned();
            $table->timestamps();

            $table->foreign('purchase_id')->references('id')->on('purchases')->cascadeOnDelete();
            $table->foreign('supply_id')->references('id')->on('supplies')->restrictOnDelete();
            $table->index('purchase_id');
            $table->index('supply_id');
        });

        Schema::table('purchases', function (Blueprint $table): void {
            $table->string('invoice_number', 100)->nullable()->after('stocking_id');
            $table->string('status', 20)->default('draft')->after('total_price');
            $table->timestamp('received_at')->nullable()->after('purchase_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            $table->dropColumn(['invoice_number', 'status', 'received_at']);
        });
        Schema::dropIfExists('purchase_items');
    }
};
