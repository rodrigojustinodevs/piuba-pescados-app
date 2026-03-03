<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     * Renames tank_types_id to tank_type_id (correct singular FK naming).
     */
    public function up(): void
    {
            Schema::table('tanks', function (Blueprint $table): void {
                $table->dropForeign('tanks_tank_types_id_foreign');
            });
            DB::statement('ALTER TABLE `tanks` CHANGE tank_types_id tank_type_id CHAR(36) NOT NULL');
            Schema::table('tanks', function (Blueprint $table): void {
                $table->foreign('tank_type_id')->references('id')->on('tank_types')->onDelete('cascade');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            Schema::table('tanks', function (Blueprint $table): void {
                $table->dropForeign('tanks_tank_type_id_foreign');
            });
            DB::statement('ALTER TABLE `tanks` CHANGE tank_type_id tank_types_id CHAR(36) NOT NULL');
            Schema::table('tanks', function (Blueprint $table): void {
                $table->foreign('tank_types_id')->references('id')->on('tank_types')->onDelete('cascade');
            });
    }
};
