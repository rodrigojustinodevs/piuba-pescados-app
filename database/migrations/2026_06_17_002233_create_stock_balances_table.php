<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::create('stock_balances', function (Blueprint $table): void {
            $table->char('id', 36)->primary();
            $table->char('stock_id', 36)->index();
            $table->char('supply_id', 36)->index();
            $table->decimal('quantity', 15, 3)->default(0);
            $table->timestamps();

            $table->unique(['stock_id', 'supply_id']);

            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
            $table->foreign('supply_id')->references('id')->on('supplies')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};
