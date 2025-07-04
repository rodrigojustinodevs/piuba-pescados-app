<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('growth_curves', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('batche_id');
            $table->float('average_weight');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('batche_id')->references('id')->on('batches');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('growth_curves');
    }
};
