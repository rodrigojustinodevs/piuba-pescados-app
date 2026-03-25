<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('stockings', function (Blueprint $table): void {
            $table->unsignedInteger('current_quantity')->nullable()->after('quantity');
            $table->decimal('estimated_biomass', 12, 4)->nullable()->after('average_weight');
        });
    }

    public function down(): void
    {
        Schema::table('stockings', function (Blueprint $table): void {
            $table->dropColumn(['current_quantity', 'estimated_biomass']);
        });
    }
};
