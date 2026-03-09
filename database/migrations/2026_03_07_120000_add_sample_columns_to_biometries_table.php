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
        Schema::table('biometries', function (Blueprint $table): void {
            $table->double('sample_weight')->default(0)->after('biometry_date')->comment('Peso total da amostra');
            $table->unsignedInteger('sample_quantity')->default(0)->after('sample_weight')->comment('Quantos peixes foram contados');
            $table->double('biomass_estimated')->nullable()->after('sample_quantity')->comment('Biomassa total do tanque no dia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('biometries', function (Blueprint $table): void {
            $table->dropColumn(['sample_weight', 'sample_quantity', 'biomass_estimated']);
        });
    }
};
