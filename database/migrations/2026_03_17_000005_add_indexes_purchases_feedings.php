<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     * Índices para listagem por empresa/período, por fornecedor e consumo por lote (plan item 10).
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            $table->index(['company_id', 'purchase_date'], 'purchases_company_id_purchase_date_index');
            $table->index(['supplier_id', 'purchase_date'], 'purchases_supplier_id_purchase_date_index');
        });

        Schema::table('feedings', function (Blueprint $table): void {
            $table->index(['batch_id', 'feeding_date'], 'feedings_batch_id_feeding_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            $table->dropIndex('purchases_company_id_purchase_date_index');
            $table->dropIndex('purchases_supplier_id_purchase_date_index');
        });
        Schema::table('feedings', function (Blueprint $table): void {
            $table->dropIndex('feedings_batch_id_feeding_date_index');
        });
    }
};
