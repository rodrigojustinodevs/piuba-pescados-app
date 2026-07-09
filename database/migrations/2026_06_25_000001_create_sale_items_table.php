<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', static function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('sale_id', 36)->index();
            $table->string('batch_id', 36)->index();
            $table->string('stocking_id', 36)->index();

            $table->string('product_name', 100)->nullable();
            $table->string('species', 100)->nullable();
            $table->string('category', 50)->nullable();

            $table->decimal('total_weight', 10, 3);
            $table->decimal('price_per_kg', 15, 2);
            $table->decimal('subtotal', 15, 2);

            // CMV snapshot — preenchido por RegisterBiomassOutflowAction após a venda
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);

            $table->boolean('is_total_harvest')->default(false);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('sales')->cascadeOnDelete();
            $table->foreign('batch_id')->references('id')->on('batches');
            $table->foreign('stocking_id')->references('id')->on('stockings');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
