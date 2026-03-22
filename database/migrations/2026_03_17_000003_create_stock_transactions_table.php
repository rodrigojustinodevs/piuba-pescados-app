<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     * Rastreamento de movimentação de estoque (entradas/saídas) com referência polimórfica.
     */
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('supply_id');
            $table->enum('reference_type', ['purchase_item', 'feeding', 'adjustment', 'transfer', 'stocking'])
                ->comment('Reference type: purchase_item, feeding, adjustment, transfer, stocking');
            $table->uuid('reference_id');
            $table->enum('direction', ['in', 'out'])->comment('Direction: in, out');
            $table->decimal('quantity', 15, 4)->unsigned();
            $table->enum('unit', ['kg', 'g', 'liter', 'ml', 'unit', 'box', 'piece'])->default('kg')->comment('Unit of measurement: kg, g, liter, ml, unit, box, piece');
            $table->decimal('unit_price', 15, 4)->unsigned()->default(0)->comment('Unit price');
            $table->uuid('created_by')->nullable()->comment('User who created the transaction');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('supply_id')->references('id')->on('supplies')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
