<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     * Renames Settlement module to Stocking (aquaculture terminology).
     */
    public function up(): void
    {
        Schema::rename('settlements', 'stockings');

        DB::statement('ALTER TABLE stockings CHANGE settlement_date stocking_date DATE NULL');
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE stockings CHANGE stocking_date settlement_date DATE NULL');

        Schema::rename('stockings', 'settlements');
    }
};
