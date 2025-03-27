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
        Schema::create('batches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tank_id');
            $table->date('entry_date');
            $table->integer('initial_quantity');
            $table->string('species', 100);
            $table->enum('status', ['active', 'finished']);
            $table->enum('cultivation', ['nursery', 'daycare']);
            $table->foreign('tank_id')->references('id')->on('tanks')->onDelete('cascade');
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
