<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    public function up(): void
    {
        Schema::table('feedings', function (Blueprint $table): void {
            $table->decimal('cost_at_time', 15, 2)->default(0.00)->after('quantity_provided')->comment('Valor da ração no momento exato do trato');
        });
    }

    public function down(): void
    {
        Schema::table('feedings', function (Blueprint $table): void {
            $table->dropColumn('cost_at_time');
        });
    }
};
