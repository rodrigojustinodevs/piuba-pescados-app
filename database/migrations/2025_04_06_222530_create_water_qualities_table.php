<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::create('water_qualities', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('tank_id')
                ->constrained('tanks')
                ->cascadeOnDelete();

            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->restrictOnDelete();

            $table->timestamp('measured_at')
                ->default(now())
                ->comment('Exact measurement moment (YYYY-MM-DD HH:MM:SS)');

            $table->decimal('ph', 5, 2)->nullable()
                ->comment('PH — ideal: 6.5–8.5');

            $table->decimal('dissolved_oxygen', 6, 2)->nullable()
                ->comment('Dissolved oxygen in mg/L — ideal: > 5 mg/L');

            $table->decimal('temperature', 5, 2)->nullable()
                ->comment('Temperature in °C');

            $table->decimal('ammonia', 7, 4)->nullable()
                ->comment('Total ammonia in mg/L — toxic above 0.1 mg/L');

            $table->decimal('salinity', 6, 2)->nullable()
                ->comment('Salinity in ppt (parts per thousand)');

            $table->decimal('turbidity', 7, 2)->nullable()
                ->comment('Turbidity in NTU');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tank_id', 'measured_at'], 'wq_tank_measured_idx');

            $table->index(['company_id', 'measured_at'], 'wq_company_measured_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_quality');
    }
};
