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
        Schema::create('transfers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('batche_id');
            $table->uuid('origin_tank_id');
            $table->uuid('destination_tank_id');
            $table->integer('quantity');
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('batche_id')->references('id')->on('batches')->onDelete('cascade');
            $table->foreign('origin_tank_id')->references('id')->on('tanks')->onDelete('cascade');
            $table->foreign('destination_tank_id')->references('id')->on('tanks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
