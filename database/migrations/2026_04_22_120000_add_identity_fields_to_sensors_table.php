<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('sensors', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('sensor_type');
            $table->string('serial_number')->nullable()->after('name');
            $table->unsignedTinyInteger('battery')->nullable()->after('serial_number');
            $table->string('unit', 20)->nullable()->after('battery');
            $table->decimal('last_reading', 10, 2)->nullable()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('sensors', function (Blueprint $table): void {
            $table->dropColumn([
                'name',
                'serial_number',
                'battery',
                'unit',
                'last_reading',
            ]);
        });
    }
};
