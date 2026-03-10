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
        Schema::table('biometries', function (Blueprint $blueprint): void {
            // density_at_time: Armazena o kg/m³ no momento desta biometria
            $blueprint->double('density_at_time')
                ->nullable()
                ->after('fcr')
                ->comment('Tank density in kg/m³');

            // recommended_ration: Sugestão de trato diário (kg) para os próximos dias
            $blueprint->double('recommended_ration')
                ->nullable()
                ->after('density_at_time')
                ->comment('Daily feeding recommendation based on % of body weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biometries', function (Blueprint $blueprint): void {
            $blueprint->dropColumn(['density_at_time', 'recommended_ration']);
        });
    }
};
