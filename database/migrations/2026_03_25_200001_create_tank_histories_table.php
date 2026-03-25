<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::create('tank_histories', function (Blueprint $table): void {
            $table->char('id', 36)->primary();
            $table->char('company_id', 36)->index();
            $table->char('tank_id', 36)->index();
            $table->enum('event', ['cleaning', 'maintenance', 'fallowing', 'status_change']);
            $table->date('event_date');
            $table->text('description')->nullable();
            $table->string('performed_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('tank_id')->references('id')->on('tanks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tank_histories');
    }
};
