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
        Schema::create('biometries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('batche_id');
            $table->date('biometry_date');
            $table->float('average_weight');
            $table->float('fcr');
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
        Schema::dropIfExists('biometries');
    }
};
