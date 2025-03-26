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
        Schema::create('stockings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('batche_id');
            $table->date('stocking_date');
            $table->integer('quantity');
            $table->float('average_weight');
            $table->timestamps();

            $table->foreign('batche_id')->references('id')->on('batches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stockings');
    }
};
