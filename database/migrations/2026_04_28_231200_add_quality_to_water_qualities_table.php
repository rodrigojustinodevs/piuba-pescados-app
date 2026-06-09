<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('water_qualities', static function (Blueprint $table): void {
            $table->enum('quality', ['excellent', 'good', 'warning', 'critical', 'unknown'])
                ->default('unknown')
                ->after('turbidity');
        });
    }

    public function down(): void
    {
        Schema::table('water_qualities', static function (Blueprint $table): void {
            $table->dropColumn('quality');
        });
    }
};
