<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Popula sale_items com os dados das vendas existentes (1 item por venda).
 * Vendas soft-deleted são incluídas para manter rastreabilidade histórica.
 * unit_cost e total_cost ficam zerados — valores históricos não são recalculáveis com precisão.
 */
return new class () extends Migration
{
    public function up(): void
    {
        DB::statement("
            INSERT INTO sale_items (
                id, sale_id, batch_id, stocking_id,
                product_name, species,
                total_weight, price_per_kg, subtotal,
                unit_cost, total_cost,
                is_total_harvest,
                created_at, updated_at
            )
            SELECT
                UUID(),
                s.id,
                s.batch_id,
                COALESCE(s.stocking_id, ''),
                COALESCE(b.name, b.species, 'Produto'),
                COALESCE(b.species, ''),
                COALESCE(s.total_weight, 0),
                COALESCE(s.price_per_kg, 0),
                COALESCE(s.total_revenue, 0),
                0,
                0,
                COALESCE(s.is_total_harvest, 0),
                s.created_at,
                s.updated_at
            FROM sales s
            LEFT JOIN batches b ON b.id = s.batch_id
            WHERE s.batch_id IS NOT NULL
              AND s.stocking_id IS NOT NULL
              AND s.id NOT IN (SELECT sale_id FROM sale_items)
        ");
    }

    public function down(): void
    {
        // Não é possível reverter de forma segura — os dados originais permanecem em sales
        DB::statement('DELETE FROM sale_items WHERE unit_cost = 0 AND total_cost = 0');
    }
};
