<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::create('stocking_histories', function (Blueprint $table): void {
            $table->char('id', 36)->primary();
            $table->char('company_id', 36)->index();
            $table->char('stocking_id', 36)->index();
            $table->enum('event', ['biometry', 'mortality', 'transfer', 'medication', 'feeding', 'harvest']);
            $table->date('event_date');
            // Used for mortality and transfer events (number of fish)
            $table->unsignedInteger('quantity')->nullable();
            // Used for biometry events (new measured average weight in grams)
            $table->decimal('average_weight', 10, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('stocking_id')->references('id')->on('stockings')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocking_histories');
    }
};
