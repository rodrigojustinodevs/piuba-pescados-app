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
        Schema::create('water_qualities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tank_id');
            $table->date('analysis_date');
            $table->float('ph');
            $table->float('oxygen');
            $table->float('temperature');
            $table->float('ammonia');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tank_id')->references('id')->on('tanks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_qualities');
    }
};
