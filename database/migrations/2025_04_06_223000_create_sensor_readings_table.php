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
        Schema::create('sensor_readings', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignUuid('sensor_id')
                ->constrained('sensors')
                ->cascadeOnDelete();

            // company_id direto na tabela — evita join com sensors/tanks
            // em toda query de listagem e garante isolamento multi-tenant
            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->restrictOnDelete();

            $table->float('value', 12)
                ->comment('Valor medido pelo sensor');

            $table->string('unit', 20)
                ->comment('Unidade da leitura: °C, pH, mg/L, ppm, etc.');

            $table->timestamp('measured_at')
                ->comment('Momento exato da leitura — pode diferir de created_at');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ── Índices para queries de séries temporais ──────────────────────

            // Consulta principal: leituras de um sensor ordenadas por data
            $table->index(['sensor_id', 'measured_at'], 'sr_sensor_measured_idx');

            // Multi-tenancy + paginação por data
            $table->index(['company_id', 'measured_at'], 'sr_company_measured_idx');

            // Filtro por sensor dentro de uma empresa (combinação mais comum)
            $table->index(['company_id', 'sensor_id', 'measured_at'], 'sr_company_sensor_measured_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
