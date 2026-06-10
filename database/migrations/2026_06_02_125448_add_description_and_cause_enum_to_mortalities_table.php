<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Map any existing free-text cause values to the nearest enum value
        DB::table('mortalities')->update(['cause' => 'unknown']);

        Schema::table('mortalities', function (Blueprint $table): void {
            $table->enum('cause', [
                'disease',
                'water_quality',
                'predation',
                'handling',
                'climate',
                'unknown',
                'other',
            ])->change();

            $table->string('description')->nullable()->after('cause');
        });
    }

    public function down(): void
    {
        Schema::table('mortalities', function (Blueprint $table): void {
            $table->string('cause', 255)->change();
            $table->dropColumn('description');
        });
    }
};
