<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feedings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('batche_id');
            $table->timestamp('feeding_date');
            $table->float('quantity_provided');
            $table->string('feed_type', 100);
            $table->float('stock_reduction_quantity');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('batche_id')->references('id')->on('batches')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedings');
    }
};
