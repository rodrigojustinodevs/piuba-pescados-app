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
        Schema::create('sensors', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tank_id');
            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->restrictOnDelete();
            $table->enum('sensor_type', ['ph', 'temperature', 'oxygen', 'ammonia'])
                ->default('temperature')
                ->comment('Type: temperature, ph, dissolved_oxygen, ammonia, etc.');
            $table->timestamp('installation_date')
                ->default(now())
                ->comment('Installation date (YYYY-MM-DD)');
            $table->enum('status', ['active', 'inactive', 'maintenance'])
                ->default('active')
                ->comment('Status: active, inactive, maintenance');

            $table->text('notes')->nullable()->comment('Notes about the sensor');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'tank_id'], 'sensors_company_tank_idx');

            // Filtro por tipo de sensor dentro de uma empresa
            $table->index(['company_id', 'sensor_type'], 'sensors_company_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensors');
    }
};
