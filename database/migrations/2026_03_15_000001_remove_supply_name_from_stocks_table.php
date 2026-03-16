<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     * supply_name removido: o dado passa a vir do relacionamento com Supplier.
     */
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table): void {
            $table->dropColumn('supply_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table): void {
            $table->string('supply_name', 255)->nullable()->after('supplier_id');
        });
    }
};
