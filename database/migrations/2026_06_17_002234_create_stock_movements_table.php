<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->char('id', 36)->primary();
            $table->char('stock_id', 36);
            $table->char('supply_id', 36);
            $table->char('user_id', 36);
            $table->enum('type', ['entry', 'exit', 'adjustment', 'transfer'])->index();
            $table->decimal('quantity', 15, 3);
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index('stock_id');
            $table->index('supply_id');
            $table->index('user_id');
            $table->index('created_at');

            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
            $table->foreign('supply_id')->references('id')->on('supplies')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
