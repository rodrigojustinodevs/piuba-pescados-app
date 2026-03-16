<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     * Vincula a alimentação ao estoque utilizado (nome do insumo vem do Supplier do Stock).
     */
    public function up(): void
    {
        Schema::table('feedings', function (Blueprint $table): void {
            $table->uuid('stock_id')->nullable()->after('feed_type');
            $table->foreign('stock_id')->references('id')->on('stocks')->nullOnDelete();
            $table->index('stock_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedings', function (Blueprint $table): void {
            $table->dropForeign(['stock_id']);
            $table->dropIndex(['stock_id']);
            $table->dropColumn('stock_id');
        });
    }
};
