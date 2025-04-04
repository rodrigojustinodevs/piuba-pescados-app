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
        Schema::create('mortalities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('batche_id');
            $table->integer('quantity');
            $table->string('cause', 255);

            $table->foreign('batche_id')->references('id')->on('batches')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mortalities');
    }
};
