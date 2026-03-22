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
        $units = ['kg', 'g', 'l', 'ml', 'un', 'thousand'];

        Schema::table('stocks', function (Blueprint $table) use ($units): void {
            $table->enum('unit', $units)->default('kg')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table): void {
            $table->string('unit', 50)->default('kg')->change();
        });
    }
};
